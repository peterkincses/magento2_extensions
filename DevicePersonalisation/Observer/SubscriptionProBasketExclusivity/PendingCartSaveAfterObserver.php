<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Observer\SubscriptionProBasketExclusivity;

use BAT\DevicePersonalisation\Api\PsnItemRepositoryInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use BAT\DevicePersonalisation\Helper\Data as PersonalisationHelper;

class PendingCartSaveAfterObserver implements ObserverInterface
{
    /**
     * @var PsnItemRepositoryInterface
     */
    private $psnItemRepository;

    /**
     * @var PersonalisationHelper
     */
    private $dataHelper;

    public function __construct(
        PsnItemRepositoryInterface $psnItemRepository,
        PersonalisationHelper $dataHelper
    ) {
        $this->psnItemRepository = $psnItemRepository;
        $this->dataHelper = $dataHelper;
    }

    public function execute(Observer $observer): void
    {
        if ($this->dataHelper->isEnabled()) {
            $pendingCart = $observer->getEvent()->getPendingCart();

            foreach ($pendingCart->getAllItems() as $item) {
                if ($item->getData('psn_item_id')) {
                    $id = (int) $item->getData('psn_item_id');
                    $psnItem = $this->psnItemRepository->getById($id);
                    $idNew = (int) $item->getId();
                    if ($item->getChildren()) {
                        $idNew = (int) $item->getChildren()[0]->getId();
                    }
                    $psnItem->setQuoteItemId($idNew);
                    $this->psnItemRepository->save($psnItem);
                }
            }
        }
    }
}
