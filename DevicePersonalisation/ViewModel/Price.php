<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\ViewModel;

use BAT\DevicePersonalisation\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Directory\Model\Currency;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Pricing\PriceCurrencyInterface as PriceCurrency;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class Price implements ArgumentInterface
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

    public function __construct(
        Data $helper,
        Currency $currency,
        PriceHelper $priceHelper,
        PriceCurrency $priceCurrency
    ) {
        $this->helper = $helper;
        $this->currency = $currency;
        $this->priceHelper = $priceHelper;
        $this->priceCurrency = $priceCurrency;
    }

    public function getFormattedPrice(float $price): ?string
    {
        return sprintf(
            '%s',
            $this->priceHelper->currency((float) $price)
        );
    }

    public function getTotalPrice(Product $product, float $personalisationPrice, bool $formatted = false): ?string
    {
        $totalPrice = (float) $product->getFinalPrice() + (float) $personalisationPrice;
        if ($formatted) {
            return sprintf(
                '%s',
                $this->priceHelper->currency($totalPrice)
            );
        }
        return (string) $totalPrice;
    }
}
