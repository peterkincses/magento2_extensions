<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Tax\Api\TaxCalculationInterface;

class TaxCalculation
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var TaxCalculationInterface
     */
    protected $taxCalculation;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ScopeConfigInterface $scopeConfig,
        TaxCalculationInterface $taxCalculation
    ) {
        $this->productRepository = $productRepository;
        $this->scopeConfig = $scopeConfig;
        $this->taxCalculation = $taxCalculation;
    }

    public function getPriceInclAndExclTax(int $productId, float $price): array
    {
        $product = $this->productRepository->getById($productId);

        if ($taxAttribute = $product->getCustomAttribute('tax_class_id')) {
            // First get base price (=price excluding tax)
            $productRateId = $taxAttribute->getValue();
            $rate = $this->taxCalculation->getCalculatedRate($productRateId);
            if (
                (int) $this->scopeConfig->getValue(
                    'tax/calculation/price_includes_tax',
                    ScopeInterface::SCOPE_STORE
                ) === 1
            ) {
                // Product price in catalog is including tax.
                $priceExcludingTax = $price / (1 + ($rate / 100));
            } else {
                // Product price in catalog is excluding tax.
                $priceExcludingTax = $price;
            }

            $priceIncludingTax = $priceExcludingTax + ($priceExcludingTax * ($rate / 100));

            return [
                'incl' => $priceIncludingTax,
                'excl' => $priceExcludingTax,
            ];
        }
        return null;
    }
}
