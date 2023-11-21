<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    public const XML_PATH_DEVICE_PERSONALISATION_ENABLED = 'bat_device_personalisation/general/enabled';
    public const XML_PATH_DEVICE_PERSONALISATION_PREVENT_FULFILMENT
        = 'bat_device_personalisation/general/prevent_fulfilment';
    public const XML_PATH_DEVICE_PERSONALISATION_PRICE = 'bat_device_personalisation/pricing/price';
    public const XML_PATH_DEVICE_PERSONALISATION_SPECIAL_PRICE = 'bat_device_personalisation/pricing/special_price';
    public const XML_PATH_DEVICE_PERSONALISATION_FREE_ENGRAVING_ENABLED
        = 'bat_device_personalisation/pricing/free_engraving_enabled';
    public const XML_PATH_DEVICE_PERSONALISATION_FREE_ENGRAVING_CUSTOMER_SEGMENTS
        = 'bat_device_personalisation/pricing/free_engraving_customer_segments';
    public const XML_PATH_DEVICE_PERSONALISATION_DISCLAIMER_COPY = 'bat_device_personalisation/disclaimer/copy';
    public const XML_PATH_DEVICE_PERSONALISATION_VALIDATION_REGEX = 'bat_device_personalisation/validation/regex';
    public const XML_PATH_DEVICE_PERSONALISATION_FULFILLMENT_REPORT_ENABLED
        = 'bat_device_personalisation/general/fullfill_report_enabled';
    public const XML_PATH_DEVICE_PERSONALISATION_VALIDATION_MAX_CHAR_HOR
        = 'bat_device_personalisation/validation/max_characters_horizontal';
    public const XML_PATH_DEVICE_PERSONALISATION_VALIDATION_MAX_CHAR_VER
        = 'bat_device_personalisation/validation/max_characters_vertical';
    public const XML_PATH_DEVICE_PERSONALISATION_PERSONALISATION_REORDER_REQUIRED
        = 'bat_device_personalisation/general/personalisation_reorder_required';
    public const XML_PATH_DEVICE_PERSONALISATION_PERSONALISATION_MESSAGE_AFTER_REMOVING_PERSONALISATION
        = 'bat_device_personalisation/general/message_after_removing_personalisation';
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var Store
     */
    protected $currentStore;

    public function __construct(
        Context $context,
        RedirectInterface $redirect,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->redirect = $redirect;
        $this->storeManager = $storeManager;
        $this->currentStore = $data['current_store'] ?? $storeManager->getStore();

        if (!$this->currentStore instanceof Store) {
            throw new InvalidArgumentException('Required store object is invalid');
        }
    }

    /**
     * @return int|mixed
     */
    public function getRequestStore()
    {
        $store = $this->storeManager->getStore()->getId();
        if ($store) {
            return $store;
        }
        $store = $this->_getRequest()->getParam('store');
        if ($store) {
            return $store;
        }
        $url = $this->redirect->getRefererUrl();
        return $this->getStoreFromReferrer($url);
    }

    /**
     * @return int|mixed
     */
    protected function getStoreFromReferrer(string $url)
    {
        $urlParts = parse_url($url);
        $path = $urlParts['path'];
        preg_match('/store\/(\d+)/', $path, $matches);
        if (isset($matches[1])) {
            return $matches[1];
        }
        return 0;
    }

    /**
     * Retrieve config flag by path
     */
    public function getConfigFlag(string $path): bool
    {
        return $this->scopeConfig->isSetFlag(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->currentStore
        );
    }

    /**
     * Retrieve config value by path
     * @return mixed
     */
    public function getConfigValue(string $path)
    {
        return $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $this->currentStore
        );
    }

    public function isEnabled(?string $storeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEVICE_PERSONALISATION_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    public function isFulfilmentPrevented(?string $storeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEVICE_PERSONALISATION_PREVENT_FULFILMENT,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    public function isFulfilmentReportEnabled(?string $storeCode = null): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEVICE_PERSONALISATION_FULFILLMENT_REPORT_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }

    public function getStoreCode(): string
    {
        return $this->storeManager->getStore()->getCode();
    }

    public function isFreeEngravingEnabled(): bool
    {
        return $this->getConfigFlag(self::XML_PATH_DEVICE_PERSONALISATION_FREE_ENGRAVING_ENABLED);
    }

    public function getFreeEngravingCustomerSegmentIds(): array
    {
        $segmentIds = $this->getConfigValue(self::XML_PATH_DEVICE_PERSONALISATION_FREE_ENGRAVING_CUSTOMER_SEGMENTS);
        return is_string($segmentIds) ? explode(',', $segmentIds) : [];
    }

    public function getMaxCharHorizontal(): string
    {
        return $this->getConfigValue(self::XML_PATH_DEVICE_PERSONALISATION_VALIDATION_MAX_CHAR_HOR);
    }

    public function getMaxCharVertical(): string
    {
        return $this->getConfigValue(self::XML_PATH_DEVICE_PERSONALISATION_VALIDATION_MAX_CHAR_VER);
    }

    public function isPersonalisationReorderRequired(): bool
    {
        return $this->getConfigFlag(self::XML_PATH_DEVICE_PERSONALISATION_PERSONALISATION_REORDER_REQUIRED);
    }

    public function getMessageAfterRemovingPersonalisation(): string
    {
        return $this->getConfigValue(self::XML_PATH_DEVICE_PERSONALISATION_PERSONALISATION_MESSAGE_AFTER_REMOVING_PERSONALISATION);
    }
}
