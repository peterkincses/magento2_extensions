<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\Quote;

use BAT\DevicePersonalisation\Model\Service\PsnItem as PsnItemService;
use Closure;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use BAT\DevicePersonalisation\Helper\Data as PersonalisationHelper;
use Magento\Framework\App\RequestInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class SplitItemPlugin
{
    /**
     * @var PsnItemService
     */
    protected $psnItemService;

    /**
     * @var PersonalisationHelper
     */
    private $dataHelper;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepo;

    public function __construct(
        PsnItemService $psnItemService,
        PersonalisationHelper $dataHelper,
        RequestInterface $request,
        ProductRepositoryInterface $productRepo
    ) {
        $this->psnItemService = $psnItemService;
        $this->dataHelper = $dataHelper;
        $this->request = $request;
        $this->productRepo = $productRepo;
    }

    /**
     * {@inheritDoc}
     */
    public function aroundRepresentProduct(QuoteItem $item, Closure $proceed, $product): ?bool
    {
        if ($this->dataHelper->isEnabled()) {
            $psnRequestData = $this->psnItemService->getFilteredParams($this->request);
            $productReloaded = $this->productRepo->getById($product->getId());
            if (!is_null($psnRequestData) || $productReloaded->getPsnIsPersonalisable()) {
                return false;
            }
        }
        return $proceed($product);
    }
}
