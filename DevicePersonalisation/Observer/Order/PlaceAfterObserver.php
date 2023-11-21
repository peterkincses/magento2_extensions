<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Observer\Order;

use BAT\DevicePersonalisation\Api\PsnItemRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use BAT\DevicePersonalisation\Model\Service\PsnItem;

class PlaceAfterObserver implements ObserverInterface
{
    /**
     * @var PsnItem
     */
    protected $psnItemService;

    /**
     * @var PsnItemRepositoryInterface
     */
    protected $psnItemRepository;

    public function __construct(
        PsnItem $psnItemService,
        PsnItemRepositoryInterface $psnItemRepository
    ) {
        $this->psnItemService = $psnItemService;
        $this->psnItemRepository = $psnItemRepository;
    }

    public function execute(Observer $observer): void
    {
        $order = $observer->getEvent()->getOrder();
        foreach ($order->getAllItems() as $orderItem) {
            $quoteItemId = (int) $orderItem->getQuoteItemId();
            $psnItem = $this->psnItemService->getItemByQuoteItemId($quoteItemId);
            if ($psnItem && $psnItem->getId()) {
                $psnItem->setOrderItemId((int) $orderItem->getId());
                $this->psnItemRepository->save($psnItem);
            }
        }
    }
}
