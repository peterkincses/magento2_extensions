<?php

declare(strict_types=1);

namespace BAT\Yoti\ViewModel;

use BAT\Yoti\Helper\Data as YotiHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\Information;
use Magento\Store\Model\ScopeInterface;

class AgeProvider implements ArgumentInterface
{
    protected const XML_PATH_CUSTOMER_SUPPORT_EMAIL = 'trans_email/ident_support/email';
    protected const CONFIG_PATH_CUSTOMER_SUPPORT_PHONE = 'general/store_information/phone';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var YotiHelper
     */
    private $yotiHelper;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        YotiHelper $yotiHelper
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->yotiHelper = $yotiHelper;
    }

    public function getStoreSupportEmail(): string
    {
        return $this->scopeConfig
            ->getValue(self::XML_PATH_CUSTOMER_SUPPORT_EMAIL, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getStoreName()
    {
        return $this->scopeConfig
            ->getValue(Information::XML_PATH_STORE_INFO_NAME, ScopeInterface::SCOPE_STORE);
    }

    /**
     * is magento double optin enabled
     */
    public function isDoubleOptIn(): bool
    {
        return $this->yotiHelper->isDoubleOptIn();
    }

    /**
     * is Selfie enabled
     */
    public function isSelfieEnabled(): bool
    {
        return $this->yotiHelper->isSelfieEnabled();
    }

    /**
     * is Doc Scan enabled
     */
    public function isDocScanEnabled(): bool
    {
        return $this->yotiHelper->isDocScanEnabled();
    }

    public function getPhoneNumber(): string
    {
        return $this->scopeConfig
            ->getValue(self::CONFIG_PATH_CUSTOMER_SUPPORT_PHONE, ScopeInterface::SCOPE_STORE);
    }

    public function getLanguageCode(): string
    {
        return $this->yotiHelper->getLanguageCode();
    }
}
