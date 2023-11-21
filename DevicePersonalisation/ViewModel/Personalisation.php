<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\ViewModel;

use BAT\DevicePersonalisation\Helper\Data;
use Magento\Directory\Model\CurrencyFactory as Currency;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface as PriceCurrency;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Personalisation implements ArgumentInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var Currency
     */
    protected $currency;

    /**
     * @var PriceHelper
     */
    protected $priceHelper;

    /**
     * @var PriceCurrency
     */
    protected $priceCurrency;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;


    public function __construct(
        Data $helper,
        Currency $currency,
        PriceHelper $priceHelper,
        PriceCurrency $priceCurrency,
        StoreManagerInterface $storeManager
    ) {
        $this->helper = $helper;
        $this->currency = $currency;
        $this->priceHelper = $priceHelper;
        $this->priceCurrency = $priceCurrency;
        $this->storeManager = $storeManager;
    }

    public function getPersonalisationPrice(bool $formatted = false, bool $removeDecimalsFromZero = false): ?string
    {
        $personalisePrice = $this->helper->getConfigValue(Data::XML_PATH_DEVICE_PERSONALISATION_PRICE);
        if ($formatted) {
            return $this->getFormattedPersonalisationPrice((float) $personalisePrice, $removeDecimalsFromZero);
        } else {
            return $personalisePrice;
        }
    }

    public function getPersonalisationSpecialPrice(bool $formatted = false, bool $removeDecimalsFromZero = false): ?string
    {
        $personaliseOffer = $this->helper->getConfigValue(Data::XML_PATH_DEVICE_PERSONALISATION_SPECIAL_PRICE);

        if ($formatted) {
            return $this->getFormattedPersonalisationPrice((float) $personaliseOffer, $removeDecimalsFromZero);
        } else {
            return $personaliseOffer;
        }
    }

    public function getCurrencySymbol(): string
    {
        $currencyCode = $this->storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->currency->create()->load($currencyCode);
        return $currency->getCurrencySymbol();
    }

    public function getPersonalisationProductDisclaimer(): ?string
    {
        return $this->helper->getConfigValue(Data::XML_PATH_DEVICE_PERSONALISATION_DISCLAIMER_COPY);
    }

    public function getValidationRegex(): ?string
    {
        return $this->helper->getConfigValue(Data::XML_PATH_DEVICE_PERSONALISATION_VALIDATION_REGEX);
    }

    private function getFormattedPersonalisationPrice(float $price, bool $removeDecimalsFromZero = false): ?string
    {
        if ($price > 0 || !$removeDecimalsFromZero) {
            return sprintf(
                '%s%s',
                ($price) ? '+ ' : '',
                $this->priceHelper->currency((float) $price)
            );
        } else {
            return $this->priceCurrency->convertAndFormat($price, false, 0);
        }
    }

    public function hasSpecialPrice(): bool
    {
        return ((float) $this->getPersonalisationPrice() > 0 && (float) $this->getPersonalisationSpecialPrice()) ? true : false;
    }

    public function getFinalPersonalisationPrice(): ?string
    {
        if ($this->hasSpecialPrice()) {
            return $this->getPersonalisationSpecialPrice();
        }
        return $this->getPersonalisationPrice();
    }

    public function getStoreCode(): string
    {
        return $this->helper->getStoreCode();
    }
}
