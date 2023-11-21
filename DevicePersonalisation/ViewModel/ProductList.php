<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\ViewModel;

use BAT\DevicePersonalisation\Helper\Data;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class ProductList
 */
class ProductList implements ArgumentInterface
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * ProductList constructor.
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Check if product has psn_is_personalisable flag
     */
    public function isPersonalisable(Product $product): bool
    {
        $flag = Data::XML_PATH_DEVICE_PERSONALISATION_ENABLED;
        if ($this->helper->getConfigFlag($flag) && $product->getPsnIsPersonalisable()) {
            return true;
        }
        return false;
    }
}
