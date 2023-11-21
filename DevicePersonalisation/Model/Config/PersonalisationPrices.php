<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Config;

use BAT\DevicePersonalisation\Model\Service\FreePersonalisationChecker;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class PersonalisationPrices
{
    public const CONFIG_PATH_PRICE          = 'bat_device_personalisation/pricing/price';
    public const CONFIG_PATH_SPECIAL_PRICE  = 'bat_device_personalisation/pricing/special_price';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var FreePersonalisationChecker
     */
    private $freePersonalisationChecker;

    /**
     * @inheritdoc
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        FreePersonalisationChecker $freePersonalisationChecker
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->freePersonalisationChecker = $freePersonalisationChecker;
    }

    public function getPrice(int $scope = null): float
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_PRICE, ScopeInterface::SCOPE_STORE, $scope);
        if (!$value) {
            return 0;
        }
        return floatval($value);
    }

    public function getSpecialPrice(int $scope = null): float
    {
        $value = $this->scopeConfig->getValue(self::CONFIG_PATH_SPECIAL_PRICE, ScopeInterface::SCOPE_STORE, $scope);
        if (!$value) {
            return 0;
        }
        return floatval($value);
    }

    public function getFinalPrice(int $scope = null): float
    {
        $price = $this->getPrice($scope);
        if (!$price || $price <= 0 || $this->freePersonalisationChecker->isCustomerEligible()) {
            return 0;
        }
        $specialPrice = $this->getSpecialPrice($scope);
        if (!$specialPrice || $specialPrice <= 0) {
            return $price;
        }
        $value = min($price, $specialPrice);
        if ($value < 0) {
            $value = 0;
        }
        return $value;
    }
}
