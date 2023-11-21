<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\DynamicBanner;

use BAT\DevicePersonalisation\Helper\Data;
use BAT\DynamicBanner\Api\BannerTypeInterface;
use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Request\Http;

class PdpDeviceBannerType implements BannerTypeInterface
{
    private const DEFAULT_LABEL = 'PDP Device Personalisation';

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    public function __construct(
        Data $helper,
        CatalogHelper $catalogHelper
    ) {
        $this->helper = $helper;
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * @inheritDoc
     */
    public function getLabel(): string
    {
        return self::DEFAULT_LABEL;
    }

    /**
     * @inheritDoc
     */
    public function canShow(Http $request): bool
    {
        return $this->helper->isEnabled()
            && $this->isPdpPage($request)
            && $this->isPersonalisableProduct();
    }

    /**
     * Check we are on the PDP page
     */
    private function isPdpPage(Http $request): bool
    {
        return $request->getFullActionName() === 'catalog_product_view';
    }

    /**
     * Check if current product is personalisable
     */
    private function isPersonalisableProduct(): bool
    {
        return ($product = $this->catalogHelper->getProduct())
            && ($product instanceof Product)
            &&  $product->getPsnIsPersonalisable();
    }
}
