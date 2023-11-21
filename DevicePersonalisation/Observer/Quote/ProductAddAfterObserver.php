<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Observer\Quote;

use BAT\DevicePersonalisation\Model\Config\PersonalisationPrices;
use BAT\DevicePersonalisation\Model\Service\PsnItem as PsnItemService;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use BAT\DevicePersonalisation\Helper\Data as PersonalisationHelper;
use Magento\Framework\App\RequestInterface;

class ProductAddAfterObserver implements ObserverInterface
{
    /**
     * @var PersonalisationPrices
     */
    protected $personalisationPrices;
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

    public function __construct(
        PersonalisationPrices $personalisationPrices,
        PsnItemService $psnItemService,
        PersonalisationHelper $dataHelper,
        RequestInterface $request
    ) {
        $this->personalisationPrices = $personalisationPrices;
        $this->psnItemService = $psnItemService;
        $this->dataHelper = $dataHelper;
        $this->request = $request;
    }

    public function execute(Observer $observer): void
    {
        if ($this->psnItemService->getFilteredParams($this->request) && $this->dataHelper->isEnabled()) {
            $item = $observer->getEvent()->getData('quote_item');
            $store = $item->getQuote()->getStoreId();
            $personalisationPrice = $this->personalisationPrices->getFinalPrice($store);
            if (!$personalisationPrice) {
                return;
            }
            $item = ($item->getParentItem() ? $item->getParentItem() : $item);
            $customPrice = $item->getPrice() + $personalisationPrice;
            $item->setCustomPrice($customPrice);
            $item->setOriginalCustomPrice($customPrice);
            $item->getProduct()->setIsSuperMode(true);
        }
    }
}
