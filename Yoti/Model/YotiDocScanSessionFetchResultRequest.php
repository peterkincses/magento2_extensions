<?php

declare(strict_types=1);

namespace BAT\Yoti\Model;

use BAT\Yoti\Helper\Data as YotiHelper;
use Exception;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;
use Yoti\DocScan\DocScanClient;
use Yoti\DocScan\DocScanClientFactory;
use Yoti\DocScan\Session\Retrieve\CheckResponse;
use Yoti\DocScan\Session\Retrieve\GeneratedMedia;
use Yoti\DocScan\Session\Retrieve\GetSessionResult;
use Yoti\DocScan\Session\Retrieve\MediaResponse;
use Yoti\DocScan\Session\Retrieve\TaskResponse;
use BAT\Yoti\Model\YotiDocScanMiddlewareSessionRequest as MiddlewareSessionRequest;

class YotiDocScanSessionFetchResultRequest
{
    public const YOTI_DOC_CHECK_COMPLETE_STATUS_CODE = 'COMPLETED';
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var YotiHelper
     */
    protected $yotiHelper;

    /**
     * @var DocScanClientFactory
     */
    protected $docScanClientFactory;

    /**
     * @var YotiDocPemKeyList
     */
    protected $yotiDocPemKeyList;
    /**
     * @var YotiDocScanMiddlewareSessionRequest
     */
    protected $yotiDocScanMiddlewareSession;
    /**
     * @var Json
     */
    private $json;

    public function __construct(
        LoggerInterface $logger,
        YotiHelper $yotiHelper,
        DocScanClientFactory $docScanClientFactory,
        YotiDocPemKeyList $yotiDocPemKeyList,
        Json $json,
        MiddlewareSessionRequest $yotiDocScanMiddlewareSession
    ) {
        $this->logger = $logger;
        $this->yotiHelper = $yotiHelper;
        $this->docScanClientFactory = $docScanClientFactory;
        $this->yotiDocPemKeyList = $yotiDocPemKeyList;
        $this->json = $json;
        $this->yotiDocScanMiddlewareSession = $yotiDocScanMiddlewareSession;
    }

    public function execute(string $sessionId, int $customerId): array
    {
        $result = [];
        if ($this->yotiHelper->getIntegrationType() == 'pnp') {
            $result = $this->executeDirectEndPoint($sessionId, $customerId);
        } else {
            $result = $this->executeMiddlewareEndPoint($sessionId, $customerId);
        }
        return $result;
    }

    /**
     * Execute direct endpoint
     */
    public function executeDirectEndPoint(string $sessionId, int $customerId): array
    {
        $result = [];
        try {
            $sdkId = $this->yotiHelper->getDocScanSdkId();
            $pemKey = $this->yotiDocPemKeyList->getCertificatesDirFullPath() . $this->yotiHelper->getDocScanPemKey();
            $this->yotiHelper->log('Yoti Doc Scan pem key ' . $this->yotiHelper->getDocScanPemKey());
            $docScanConfig = [];
            $sandboxApiUrl = $this->yotiHelper->getDocScanSandboxApiUrl();
            if ($this->yotiHelper->isDocScanSandboxMode() && !empty($sandboxApiUrl)) {
                $docScanConfig['api.url'] = $sandboxApiUrl;
                $this->yotiHelper->log('Yoti Doc Sandbox Url ' . $sandboxApiUrl);
            }
            /** @var DocScanClient $client */
            $client = $this->docScanClientFactory
                ->create(['sdkId' => $sdkId, 'pem' => $pemKey, 'options' => $docScanConfig]);
            if ($response = $client->getSession($sessionId)) {
                $result = $this->processResult($response, $sessionId, $customerId);
                $this->yotiHelper->log('Yoti Doc Process Result', false, $result);
            } else {
                $result['error_message'] = __('Sorry, something went wrong.');
                $result['status'] = 3;
            }
        } catch (Exception $e) {
            $result['error_message'] = __($e->getMessage());
            $result['status'] = 3;
            $this->logger->error('Yoti Doc Scan API Error:' . $e->getMessage());
        }
        $this->yotiHelper->log('Yoti Doc Scan Final Result', false, $result);
        return $result;
    }

    protected function processResult(GetSessionResult $response, string $sessionId, int $customerId): array
    {
        $this->yotiHelper->log('Yoti Doc Scan Overall State: ' . $response->getState());
        if ($response->getState() == 'COMPLETED') {
            $result = $this->processDocScanData($response, $sessionId, $customerId);
        } elseif ($response->getState() == 'ONGOING') {
            $result['error_message'] = __('We are still checking your document.');
            $result['status'] = 2;
        } elseif ($response->getState() == 'EXPIRED') {
            $result['error_message'] = __('Your session epired, please try again.');
            $result['status'] = 3;
        }

        return $result;
    }

    protected function processDocScanData(GetSessionResult $data, string $sessionId, int $customerId): array
    {
        $result['status'] = 0;
        if (!$this->isDocumentValid($data)) {
            $this->yotiHelper->updateAccStatusDetails(
                $customerId,
                'document provided is not valid'
            );

            if ($this->isDocumentNotAvailable($data)) {
                $result['status'] = 4;
                $result['error_message'] = __('We were unable to read your ID information due to the image quality.');
            }

            return $result;
        }
        $textExtractData = $this->getDocScanTextExtractedData($data, $sessionId);
        $this->yotiHelper->log(json_encode($textExtractData));

        if ($this->yotiHelper->getIntegrationType() == 'pnp') {
            $result['status'] = $this->yotiHelper->getDocScanAgeVerificationStatus($textExtractData, $customerId);
        } else {
            $result['status'] = $this->yotiHelper->getDocScanAgeVerificationMiddlewareStatus($textExtractData, $customerId);
        }
        return $result;
    }

    protected function isDocumentValid(GetSessionResult $data): bool
    {
        $result = false;
        $checks = $data->getChecks();
        if (!is_array($checks)) {
            return $result;
        }

        $faceMatchCheckStatus = false;

        foreach ($checks as $check) {
            if (!empty($check->getType())) {
                $faceMatchCheckStatus = $this->getCheckStatus($check);
                if (!$faceMatchCheckStatus) {
                    break;
                }
            }
        }
        $result = ($faceMatchCheckStatus) ? true : false;
        return $result;
    }

    protected function getCheckStatus(CheckResponse $check): bool
    {
        $result = false;
        $this->yotiHelper->log('Yoti Doc Scan State For ' . $check->getType() . ':' . $check->getState());
        if (!empty($check->getState()) && $check->getState() == 'DONE' && !empty($check->getReport())) {
            $report = $check->getReport();
            if (!empty($report->getRecommendation())) {
                $recomm = $report->getRecommendation();
                $result = (!empty($recomm->getValue()) && $recomm->getValue() == 'APPROVE') ? true : false;
                $this->yotiHelper->log(
                    'Yoti Doc Scan Recommendation For ' . $check->getType() . ':' . $recomm->getValue()
                );
            }
        }
        return $result;
    }

    protected function isDocumentNotAvailable(GetSessionResult $data): bool
    {
        $result = false;
        $checks = $data->getChecks();

        if (!is_array($checks)) {
            return $result;
        }

        foreach ($checks as $check) {
            if (!empty($check->getType()) && $check->getType() === 'ID_DOCUMENT_FACE_MATCH') {
                $status = $this->getCheckRecommendationStatus($check);
                if ($status === 'NOT_AVAILABLE') {
                    $result = true;
                    break;
                }
            }
        }

        return $result;
    }

    protected function getCheckRecommendationStatus(CheckResponse $check): ?string
    {
        $result = null;
        if (!empty($check->getState()) && $check->getState() == 'DONE' && !empty($check->getReport())) {
            $report = $check->getReport();
            if (!empty($report->getRecommendation())) {
                $recomm = $report->getRecommendation();
                $result = $recomm->getValue();
            }
        }
        return $result;
    }

    protected function getDocScanTextExtractedData(GetSessionResult $data, string $sessionId): array
    {
        $result = [];
        if ($resources = $data->getResources()) {
            $idDocuments = $resources->getIdDocuments();
            if (!count($idDocuments)) {
                return $result;
            }
            foreach ($idDocuments as $idDocument) {
                $documentFields = $idDocument->getDocumentFields();
                $mediaResponse = (!empty($documentFields)) ? $documentFields->getMedia() : null;
                if (empty($mediaResponse)) {
                    $this->yotiHelper->log('Yoti Doc Scan No Media Found For Text Extraction');
                    continue;
                }
                $data = $this->getTextExtractedDataFromDocumentField($mediaResponse, $sessionId);
                $textExtractData = array_merge($result, $data);
                if (is_array($textExtractData) && array_key_exists('date_of_birth', $textExtractData)) {
                    return $textExtractData;
                }
                $result = (is_array($textExtractData) && count($textExtractData)) ? $textExtractData : [];
            }
        }

        return $result;
    }

    protected function getTextExtractedDataFromDocumentField(MediaResponse $media, string $sessionId): array
    {
        $result = [];
        if ($id = $media->getId()) {
            if ($this->yotiHelper->getIntegrationType() == 'pnp') {
                $result = $this->getTextExtractByMediaId($id, $sessionId);
            } else {
                $result = $this->getTextExtractByMediaIdMiddleware($id, $sessionId);
            }
        }
        return $result;
    }

    /**
     * text extracted using direct point
     */
    protected function getTextExtractByMediaId(string $id, string $sessionId): array
    {
        $result = [];
        $sdkId = $this->yotiHelper->getDocScanSdkId();
        $pemKey = $this->yotiDocPemKeyList->getCertificatesDirFullPath() . $this->yotiHelper->getDocScanPemKey();
        $this->yotiHelper->log('Yoti Doc Scan pem key ' . $pemKey);
        $docScanConfig = [];
        $sandboxApiUrl = $this->yotiHelper->getDocScanSandboxApiUrl();
        if ($this->yotiHelper->isDocScanSandboxMode() && !empty($sandboxApiUrl)) {
            $docScanConfig['api.url'] = $sandboxApiUrl;
        }
        /** @var DocScanClient $client */
        $client = $this->docScanClientFactory
            ->create(['sdkId' => $sdkId, 'pem' => $pemKey, 'options' => $docScanConfig]);
        if ($data = $client->getMediaContent($sessionId, $id)) {
            if (($content = $data->getContent()) && !empty($content)) {
                if (($content = $this->json->unserialize($content)) && is_array($content)) {
                    $result = $content;
                    unset($content);
                } else {
                    $this->yotiHelper->log('Yoti Doc Scan Text Extraction Content Empty');
                }
            }
        }
        return $result;
    }

    /**
     * text extracted using middleware
     */
    protected function getTextExtractByMediaIdMiddleware(string $id, string $sessionId): array
    {
        $result = [];
        if ($data = $this->yotiDocScanMiddlewareSession->readMediaDataFromSession($sessionId, $id)) {
            if (($content = $data->getBody()) && !empty($content)) {
                if (($content = $this->json->unserialize($content)) && is_array($content)) {
                    $result = $content;
                    unset($content);
                } else {
                    $this->yotiHelper->log('Yoti Doc Scan Text Extraction Content Empty');
                }
            }
        }
         $this->yotiHelper->log("Middleware Extracted " . json_encode($result));
        return $result;
    }

    /**
     * Execute Middleware Endpoint
     */
    protected function executeMiddlewareEndPoint(string $sessionId, int $customerId): array
    {
        $result = [];
        try {
            if ($response = $this->yotiDocScanMiddlewareSession->readDocScanSession($sessionId)) {
                $result = $this->processResult($response, $sessionId, $customerId);
                $this->yotiHelper->log('Yoti Doc Process Result', false, $result);
            } else {
                $result['error_message'] = __('Sorry, something went wrong.');
                $result['status'] = 3;
            }
        } catch (Exception $e) {
            $result['error_message'] = __($e->getMessage());
            $result['status'] = 3;
            $this->logger->error('Yoti Doc Scan API Error:' . $e->getMessage());
        }
        $this->yotiHelper->log('Yoti Doc Scan Final Result', false, $result);

        return $result;
    }
}
