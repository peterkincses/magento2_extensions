<?php

declare(strict_types=1);

namespace BAT\Yoti\Model;

use BAT\Yoti\Helper\Data as YotiHelper;
use Exception;
use Magento\Directory\Model\Country;
use Psr\Log\LoggerInterface;
use Yoti\DocScan\DocScanClient;
use Yoti\DocScan\DocScanClientFactory;
use Yoti\DocScan\Session\Create\Check\RequestedFaceMatchCheckBuilder;
use Yoti\DocScan\Session\Create\Check\RequestedLivenessCheckBuilder;
use Yoti\DocScan\Session\Create\SdkConfigBuilder;
use Yoti\DocScan\Session\Create\SessionSpecification;
use Yoti\DocScan\Session\Create\SessionSpecificationBuilder;
use Yoti\DocScan\Session\Create\Task\RequestedTextExtractionTaskBuilder;
use BAT\Yoti\Model\YotiDocScanMiddlewareSessionRequest as MiddlewareSessionRequest;

class YotiDocScanSessionRequest
{
    /**
     * @var SessionSpecificationBuilder
     */
    protected $sessionSpecificationBuilder;

    /**
     * @var SdkConfigBuilder
     */
    protected $sdkConfigBuilder;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var YotiHelper
     */
    protected $yotiHelper;

    /**
     * @var Country
     */
    protected $country;

    /**
     * @var YotiDocPemKeyList
     */
    protected $yotiDocPemKeyList;

    /**
     * @var RequestedTextExtractionTaskBuilder
     */
    protected $requestedTextExtractionTaskBuilder;

    /**
     * @var RequestedFaceMatchCheckBuilder
     */
    protected $requestedFaceMatchCheckBuilder;

    /**
     * @var RequestedLivenessCheckBuilder
     */
    protected $requestedLivenessCheckBuilder;

    /**
     * @var DocScanClientFactory
     */
    protected $docScanClientFactory;

    /**
     * @var yotiDocScanMiddlewareSession;
     */
    protected $yotiDocScanMiddlewareSession;

    public function __construct(
        SessionSpecificationBuilder $sessionSpecificationBuilder,
        SdkConfigBuilder $sdkConfigBuilder,
        LoggerInterface $logger,
        YotiHelper $yotiHelper,
        Country $country,
        YotiDocPemKeyList $yotiDocPemKeyList,
        RequestedTextExtractionTaskBuilder $requestedTextExtractionTaskBuilder,
        RequestedFaceMatchCheckBuilder $requestedFaceMatchCheckBuilder,
        RequestedLivenessCheckBuilder $requestedLivenessCheckBuilder,
        DocScanClientFactory $docScanClientFactory,
        MiddlewareSessionRequest $yotiDocScanMiddlewareSession
    ) {
        $this->sessionSpecificationBuilder = $sessionSpecificationBuilder;
        $this->sdkConfigBuilder = $sdkConfigBuilder;
        $this->logger = $logger;
        $this->yotiHelper = $yotiHelper;
        $this->country = $country;
        $this->yotiDocPemKeyList = $yotiDocPemKeyList;
        $this->requestedTextExtractionTaskBuilder = $requestedTextExtractionTaskBuilder;
        $this->requestedFaceMatchCheckBuilder = $requestedFaceMatchCheckBuilder;
        $this->requestedLivenessCheckBuilder = $requestedLivenessCheckBuilder;
        $this->docScanClientFactory = $docScanClientFactory;
        $this->yotiDocScanMiddlewareSession = $yotiDocScanMiddlewareSession;
    }

    public function execute(): array
    {
        $result = [];
        if ($this->yotiHelper->getIntegrationType() == 'pnp') {
            $result = $this->executeDirectEndPoint();
        } else {
            $result = $this->executeMiddlewarePoint();
        }
        return $result;
    }

    /*
     * Execute Middleware Endpoint
     */

    public function executeDirectEndPoint(): array
    {
        $result = [];
        try {
            $sdkId = $this->yotiHelper->getDocScanSdkId();
            $pemKey = $this->yotiDocPemKeyList->getCertificatesDirFullPath() . $this->yotiHelper->getDocScanPemKey();
            $this->yotiHelper->log('Yoti Doc Scan pem key ' . $pemKey);
            $docScanConfig = [];
            $sandboxApiUrl = $this->yotiHelper->getDocScanSandboxApiUrl();

            if ($this->yotiHelper->isDocScanSandboxMode() && !empty($sandboxApiUrl)) {
                $docScanConfig['api.url'] = $sandboxApiUrl;
                $this->yotiHelper->log('Yoti Doc scan sandbox api url ' . $sandboxApiUrl);
            }

            /** @var DocScanClient $client */
            $client = $this->docScanClientFactory
                ->create(['sdkId' => $sdkId, 'pem' => $pemKey, 'options' => $docScanConfig]);
            $sessionSpec = $this->createYotiDocScanSessionSpecs();
            //$this->yotiHelper->log('Payload' . json_encode($sessionSpec));
            if ($session = $client->createSession($sessionSpec)) {
                $result['sessionID'] = $session->getSessionId();
                $result['sessionToken'] = $session->getClientSessionToken();
            }
        } catch (Exception $e) {
            $result['error_message'] = $e->getMessage();
            $this->logger->error('Yoti Doc Scan API Error:' . $e->getMessage());
        }
        $this->yotiHelper->log('Yoti Doc Scan Session Request', false, $result);
        return $result;
    }

    /*
     * Execute the direct Endpoint
     */

    protected function createYotiDocScanSessionSpecs(): SessionSpecification
    {
        $sdkConfigBuilder = $this->sdkConfigBuilder
            ->withAllowsCameraAndUpload()
            ->withPrimaryColour('#2d9fff')
            ->withSecondaryColour('#FFFFFF')
            ->withFontColour('#FFFFFF');

        if ($locale = $this->yotiHelper->getLocaleForYotiDocScan()) {
            $sdkConfigBuilder->withLocale($locale);
            $this->yotiHelper->log('Yoti Doc Scan Session Request Locale ' . $locale);
        }

        $iso2CountryCode = $this->yotiHelper->getCountryCode();
        if (!empty($iso2CountryCode) && ($countryModel = $this->country->load(strtoupper($iso2CountryCode)))) {
            if ($countryModel->getId()) {
                $iso3Code = $countryModel->getData('iso3_code');
                $sdkConfigBuilder->withPresetIssuingCountry($iso3Code);
                $this->yotiHelper->log('Yoti Doc Scan Session Request Country ' . $iso3Code);
            }
        }
        $sessionSpec = $this->sessionSpecificationBuilder
            ->withRequestedTask(
                $this->requestedTextExtractionTaskBuilder
                    ->withManualCheckFallback()
                    ->build()
            );

        if (!$this->yotiHelper->isLivenessDisabled()) {
            $sessionSpec = $this->sessionSpecificationBuilder->withRequestedCheck(
                $this->requestedLivenessCheckBuilder
                    ->forZoomLiveness()
                    ->build()
            );
        }

        if (!$this->yotiHelper->isFaceMatchDisabled()) {
            $sessionSpec = $this->sessionSpecificationBuilder->withRequestedCheck(
                $this->requestedFaceMatchCheckBuilder
                    ->withManualCheckFallback()
                    ->build()
            );
        }

        $sessionSpec = $this->sessionSpecificationBuilder->withBlockBiometricConsent(true)
            ->withSdkConfig($sdkConfigBuilder->build())
            ->build();
        return $sessionSpec;
    }

    public function executeMiddlewarePoint(): array
    {
        $result = [];
        try {
            $sessionData = $this->yotiDocScanMiddlewareSession->createDocSession();
            // $sessionData = json_decode($session->getBody(),0,true);
            if (isset($sessionData['session_id']) && isset($sessionData['client_session_token'])) {
                $result['sessionID'] = $sessionData['session_id'];
                $result['sessionToken'] = $sessionData['client_session_token'];
            } else {
                $this->yotiHelper->log('Unable to create session ');
            }
        } catch (Exception $e) {
            $result['error_message'] = $e->getMessage();
            $this->logger->error('Yoti Doc Scan API Error:' . $e->getMessage());
        }
        return $result;
    }
}
