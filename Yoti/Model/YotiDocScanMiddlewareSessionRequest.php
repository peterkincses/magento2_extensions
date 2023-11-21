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
use Magento\Directory\Model\Country;
use Yoti\DocScan\Session\Retrieve\GetSessionResult;

class YotiDocScanMiddlewareSessionRequest
{

    private const CAMERA_AND_UPLOAD = 'CAMERA_AND_UPLOAD';
    private const ID_DOCUMENT_AUTHENTICITY = 'ID_DOCUMENT_AUTHENTICITY';
    private const CHECKS_MANUAL_CHECK = 'NEVER';
    private const ID_DOCUMENT_TEXT_DATA_EXTRACTION = 'ID_DOCUMENT_TEXT_DATA_EXTRACTION';
    private const TASKS_MANUAL_CHECK = 'FALLBACK';
    private const CHECK_TYPE_LIVENESS = 'LIVENESS';
    private const CHECK_TYPE_ID_DOCUMENT_FACE_MATCH = 'ID_DOCUMENT_FACE_MATCH';
    private const LIVENESS_TYPE = 'ZOOM';
    private const LIVENESS_MAX_RETRIES = 1;
    private const ID_DOCUMENT_FACE_MATCH_MANUAL_CHECK = 'FALLBACK';

    /**
     * @var Curl
     */
    public $curl;
    /**
     * @var Country
     */
    protected $country;
    /**
     * @var sessionId
     */
    protected $sessionID;
    /**
     * @var mediaId
     */
    protected $mediaID;
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

    public function __construct(
        Curl $curl,
        Json $json,
        YotiHelper $yotiHelper,
        ZendClient $httpClient,
        LoggerInterface $logger,
        Country $country
    ) {
        $this->curl = $curl;
        $this->json = $json;
        $this->yotiHelper = $yotiHelper;
        $this->httpClient = $httpClient;
        $this->logger = $logger;
        $this->country = $country;
    }

    /**
     * Create DocScan Session
     * @return mixed|void
     */
    public function createDocSession()
    {
        $sessionSpecs = $this->createDocSessionSpecs();
        $apiUrl = $this->yotiHelper->getEndpointUrl() . '/' . $this->yotiHelper->getCreateSessionEndPoint();
        $sessionData = $this->curlRequest($apiUrl, Zend_Http_Client::POST, 'create', $sessionSpecs);
        return $this->json->unserialize($sessionData->getBody());
    }

    /**
     * return session specs
     */
    protected function createDocSessionSpecs(): array
    {
        $specs = [];
        $specs['clientSessionTokenTTL'] = 600;
        $specs['resourcesTTL'] = 87000;
        $specs['authType'] = 'Basic';

        $specs['requestedChecks'] [] = ['type' => self::ID_DOCUMENT_AUTHENTICITY,
            'config' => ['manualCheck' => self::CHECKS_MANUAL_CHECK],
        ];

        if (!$this->yotiHelper->isLivenessDisabled()) {
            $specs['requestedChecks'] [] = ['type' => self::CHECK_TYPE_LIVENESS,
                'config' => [
                    'livenessType' => self::LIVENESS_TYPE,
                    'maxRetries' => self::LIVENESS_MAX_RETRIES,
                ],
            ];
        }

        if (!$this->yotiHelper->isFaceMatchDisabled()) {
            $specs['requestedChecks'] [] = ['type' => self::CHECK_TYPE_ID_DOCUMENT_FACE_MATCH,
                'config' => ['manualCheck' => self::ID_DOCUMENT_FACE_MATCH_MANUAL_CHECK],
            ];
        }

        $specs['requestedTasks'][] = ['type' => self::ID_DOCUMENT_TEXT_DATA_EXTRACTION,
            'config' => ['manualCheck' => self::TASKS_MANUAL_CHECK],
        ];

        $specs['sdkConfig'] = ['allowedCaptureMethods' => self::CAMERA_AND_UPLOAD,
            'primaryColour' => '#2D9FFF',
            'secondaryColour' => '#FFFFFF',
            'fontColour' => '#FFFFFF',
            'presetIssuingCountry' => ($this->getISOCountryCode()) ? $this->getISOCountryCode() : null,
            'successUrl' => null,
            'endUrl' => null,
            'attemptsConfiguration' => [
                'iDDocumentTextDataExtraction' => [
                    'generic' => 3,
                    'reclassification' => $this->yotiHelper->getYotiDocScanAttemptsAllowed(),
                ],
            ],
        ];

        if ($locale = $this->yotiHelper->getLocaleForYotiDocScan()) {
            $specs['sdkConfig']['locale'] = $locale;
        }

        $specs['blockBiometricConsent'] = true;
        return $specs;
    }


    /**
     * get iso3 country code
     */
    protected function getISOCountryCode(): ?string
    {
        $iso3Code = null;
        $iso2CountryCode = $this->yotiHelper->getDocScanCountryCode();
        if (!empty($iso2CountryCode) && ($countryModel = $this->country->load(strtoupper($iso2CountryCode)))) {
            if ($countryModel->getId()) {
                $iso3Code = $countryModel->getData('iso3_code');
                $this->yotiHelper->log('Yoti Doc Scan Session Request Country ' . $iso3Code);
            }
        }
        return $iso3Code;
    }

    /**
     * @return mixed|void
     */
    private function curlRequest(string $url, string $method, string $scope = 'create', array $data = [])
    {
        $this->yotiHelper->log('Yoti DOC SESSION API URL - ' . $url);
        $this->yotiHelper->log('Yoti DOC SESSION PAYLOAD - ' . $this->json->serialize($data));
        $this->httpClient->setHeaders($this->getBearerToken($scope));
        $this->httpClient
            ->setUri($url)
            ->setMethod($method);
        if (count($data)) {
            $this->httpClient->setRawData($this->json->serialize($data), 'application/json');
        }


        $responseObject = $this->httpClient->request();
        $this->yotiHelper->log('Yoti DOC SESSION RESPONSE - ' . $responseObject->getBody());
        return $responseObject;
    }

    /**
     * @return mixed[]
     */
    private function getBearerToken(string $scope = 'create'): array
    {
        $headers = [];
        if ($accessToken = $this->fetchAccessToken($scope)) {
            $headers['Authorization'] = "Bearer {$accessToken}";
        }
            $headers['country'] = $this->yotiHelper->getDocScanCountryCode();
        $this->yotiHelper->log('Bearer header:' . print_r($headers, true));
        return $headers;
    }

    /**
     * @return mixed|void
     */
    private function fetchAccessToken(string $scope = 'create')
    {

        switch ($scope) {
            case 'readcontent':
                $scope = $this->yotiHelper->getOAuthScopeReadContent();
                break;
            case 'readsession':
                $scope = $this->yotiHelper->getOAuthScopeReadSession();
                break;
            case 'delete':
                $scope = $this->yotiHelper->getOAuthScopeDeleteSession();
                break;
            default:
                $scope = $this->yotiHelper->getOAuthScope();
                break;
        }
        $authUrl = $this->yotiHelper->getOAuthUrl() . '&scope=' . $scope;

        try {
            $this->httpClient
                ->setUri($authUrl)
                ->setMethod(Zend_Http_Client::POST)
                ->setHeaders($this->getHeaders());
            $responseObject = $this->httpClient->request();
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
                //$this->yotiHelper->log('access token:' . $apiResponse['access_token']);
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
        $username = $this->yotiHelper->getOAuthUsername();
        $password = $this->yotiHelper->getOAuthPassword();
        $headers = [];
        $headers[] = 'Authorization: Basic ' . base64_encode($username . ':' . $password);
        return $headers;
    }

    /**
     * Read Session
     * @return mixed|void
     */

    public function readDocScanSession(string $sessionID)
    {
        $apiUrl = $this->yotiHelper->getEndpointUrl() . '/' . str_replace('{sessionId}', $sessionID, $this->yotiHelper->getReadSessionEndPoint());
        $sessionData = $this->curlRequest($apiUrl, Zend_Http_Client::GET, 'readsession');
        $result = json_decode($sessionData->getBody(), true);
        return new GetSessionResult($result);
    }

    /**
     * Read Media API
     * @return mixed|void
     */
    public function readMediaDataFromSession(string $sessionID, string $mediaID)
    {
        $mediaEndpoint = str_replace('{sessionId}', $sessionID, $this->yotiHelper->getReadMediaSessionEndPoint());
        $mediaEndpoint = str_replace('{mediaId}', $mediaID, $mediaEndpoint);
        $apiUrl = $this->yotiHelper->getEndpointUrl() . '/' . $mediaEndpoint;
        $mediaData = $this->curlRequest($apiUrl, Zend_Http_Client::GET, 'readcontent');
        return $mediaData;
    }

    /**
     * Delete Session
     * @return mixed|void
     */
    public function deleteDocScanSession(string $sessionID)
    {
        $apiUrl = $this->yotiHelper->getEndpointUrl() . '/' . str_replace('{sessionId}', $sessionID, $this->yotiHelper->getDeleteSessionEndPoint());
        $sessionData = $this->curlRequest($apiUrl, Zend_Http_Client::DELETE, 'delete');
        return $sessionData;
    }
}
