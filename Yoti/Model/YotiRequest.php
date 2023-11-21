<?php

declare(strict_types=1);

namespace BAT\Yoti\Model;

use BAT\Yoti\Helper\Data as YotiHelper;
use Exception;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Framework\HTTP\ZendClient;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Zend_Http_Client;
use Zend_Http_Response;

class YotiRequest
{
    /**
     * @var Curl
     */
    public $curl;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var YotiHelper
     */
    private $yotiHelper;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CustomErrorMessageList
     */
    private $customErrorMessageList;

    public function __construct(
        Curl $curl,
        Json $json,
        YotiHelper $yotiHelper,
        ZendClient $httpClient,
        LoggerInterface $logger,
        CustomErrorMessageList $customErrorMessageList
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->yotiHelper = $yotiHelper;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->customErrorMessageList = $customErrorMessageList;
    }

    public function execute(array $data): array
    {
        $result = [];
        try {
            //get Api Endpoint from config
            $apiUrl = $this->yotiHelper->getAPIUrl();
            $this->yotiHelper->log('Yoti API Url:' . $apiUrl);
            $customerReferece = $this->yotiHelper->getCustomerReference();
            $countryCode = $this->yotiHelper->getCountryCode();
            if (empty($customerReferece) || empty($countryCode)) {
                $result['code'] = 500;
                $result['error_message'] = __('Internal error.');
                $this->yotiHelper->log('Yoti Request: Customer Reference or Country code is empty');
                return $result;
            }
            $data['customerReference'] = $customerReferece;
            $data['countryCode'] = $countryCode;
            $data['device'] = (isset($data['device'])) ? $data['device'] : 'unknown';
            $this->httpClient->setHeaders($this->getBearerToken());
            $config = [
                'timeout' => $this->yotiHelper->getApiTimeout(),
                'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
            ];
            $isMock = ($this->yotiHelper->isLive() == 1) ? 0 : 1;
            $this->httpClient->setHeaders('mock', $isMock);
            $this->httpClient
                ->setUri($apiUrl)
                ->setMethod(ZendClient::POST)
                ->setConfig($config);
            $this->httpClient->setRawData($this->json->serialize($data), 'application/json');
            $this->yotiHelper->log(
                'Yoti API request: ',
                false,
                [
                    'customerReference' => $customerReferece,
                    'countryCode' => $countryCode,
                    'device' => $data['device'],
                ]
            );

            $apiRawResponse = $this->httpClient->request();
            $this->yotiHelper->log(
                'Yoti API Full response: ',
                false,
                ['body' => $apiRawResponse->asString()]
            );
            $result = $this->processResponse($apiRawResponse);
        } catch (Exception $e) {
            $this->logger->debug('Yoti API Error:' . $e->getMessage());
        }
        return $result;
    }

    private function processResponse(Zend_Http_Response $responseObject): array
    {
        $response = [];
        if (!$responseObject->isSuccessful()) {
            $response['code'] = $responseObject->getStatus();
            $response['error_message'] = $responseObject->getMessage();
            if ($apiResponse = $responseObject->getBody()) {
                $apiResponse = $this->json->unserialize($apiResponse);
                if (
                    is_array($apiResponse)
                    && isset($apiResponse['targetErrorTrace'])
                    && isset($apiResponse['targetErrorTrace']['targetError'])
                    && $this->yotiHelper->isJson($apiResponse['targetErrorTrace']['targetError'])
                ) {
                    $errorData = $this->json->unserialize($apiResponse['targetErrorTrace']['targetError']);
                    $errorMsg = (is_array($errorData) && isset($errorData['error_message']))
                        ? $errorData['error_message']
                        : 'Error encountered while processing the image';
                    $customErrorMessage = null;
                    if (isset($errorData['error_code'])) {
                        $customErrorMessage = $this->customErrorMessageList
                            ->getErrorMessageByCode($errorData['error_code']);
                        $customErrorMessage .= ' ' . __('Please retry or contact the customer care team.');
                    }
                    $response['error_message'] = (!empty($customErrorMessage)) ? $customErrorMessage : __($errorMsg);
                    $response['error_code'] = $errorData['error_code'] ?? 0;
                } elseif (!empty($apiResponse) && is_array($apiResponse) && isset($apiResponse['message'])) {
                    $response['error_message'] = __($apiResponse['message']);
                }
            }
            $this->yotiHelper->log('API: Unsuccessful HTTP response code: '
                . $responseObject->getStatus() . ' ' . $responseObject->getMessage());
            return $response;
        }

        if ($apiResponse = $responseObject->getBody()) {
            $apiResponse = $this->json->unserialize($apiResponse);
            if ($apiResponse === null) {
                $response['code'] = $responseObject->getStatus();
                $response['error_message'] = __('API: Could not decode JSON response body');
                $this->yotiHelper->log('API: Could not decode JSON response body');
                return $response;
            }
        } elseif (in_array($responseObject->getStatus(), [201, 202])) {
            $apiResponse = $responseObject->getMessage();
        }
        $this->yotiHelper->log('Yoti API response: '
            . $responseObject->getStatus(), false, ['body' => $apiResponse]);
        return $this->prepareFromRawData($apiResponse);
    }

    public function prepareFromRawData(array $apiResponse): array
    {
        if (is_array($apiResponse)) {
            if (isset($apiResponse['antispoofing']['prediction'])) {
                $apiResponse['anti_spoof_prediction'] = $apiResponse['antispoofing']['prediction'];
            }
            if (isset($apiResponse['age']['age'])) {
                $apiResponse['predAge'] = $apiResponse['age']['age'];
            }
            if (isset($apiResponse['age']['st_dev'])) {
                $apiResponse['uncertainty'] = $apiResponse['age']['st_dev'];
            }
            return $apiResponse;
        }
        return [];
    }

    /**
     * @return mixed|void
     */
    private function fetchAccessToken()
    {
        try {
            $this->httpClient
                ->setUri($this->yotiHelper->getAuthUrl())
                ->setMethod(Zend_Http_Client::POST)
                ->setHeaders($this->getHeaders());
            $responseObject = $this->httpClient->request();
            $authUrl = $this->yotiHelper->getAuthUrl();
            $logMsg = 'Yoti Token Request:' . $authUrl . '---' . print_r($this->getHeaders(), true);
            $this->yotiHelper->log($logMsg);

            if (!$responseObject->isSuccessful()) {
                $this->logger->critical('Yoti Authentication Error: ' . $responseObject->getMessage());
                return;
            }

            if ($apiResponse = $responseObject->getBody()) {
                $apiResponse = $this->json->unserialize($apiResponse);
                if ($apiResponse === null) {
                    $this->logger->critical('Yoti: Could not decode JSON response body');
                }
            }

            if (empty($apiResponse['access_token']) || empty($apiResponse['expires_in'])) {
                $logMsg = 'Yoti: Blank access token or expiry received during access token request';
                $this->logger->critical($logMsg);
            } else {
                $this->yotiHelper->log('access token:' . $apiResponse['access_token']);
                return $apiResponse['access_token'];
            }
        } catch (Exception $e) {
            $this->logger->critical('Yoti Authentication Error: ' . $e->getMessage());
        }
    }

    /**
     * @return array<mixed>
     */
    private function getHeaders(): array
    {
        $username = $this->yotiHelper->getAuthUsername();
        $password = $this->yotiHelper->getAuthPassword();
        $headers = [];
        $headers[] = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
        return $headers;
    }

    /**
     * @return mixed[]
     */
    private function getBearerToken(): array
    {
        $headers = ['Accept-encoding' => 'utf-8'];
        if ($accessToken = $this->fetchAccessToken()) {
            $headers['Authorization'] = "Bearer {$accessToken}";
        }
        $this->yotiHelper->log('Bearer header:' . print_r($headers, true));
        return $headers;
    }
}
