<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Observer\SubscriptionProBasketExclusivity;

use BAT\DevicePersonalisation\Model\Config\PersonalisationPrices;
use BAT\DevicePersonalisation\Model\Service\PsnItem;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use BAT\DevicePersonalisation\Helper\Data as PersonalisationHelper;

class ItemAddAfterObserver implements ObserverInterface
{
    /**
     * @var PsnItem
     */
    private $psnItemService;

    /**
     * @var PersonalisationHelper
     */
    private $dataHelper;

    /**
     * @var PersonalisationPrices
     */
    private $personalisationPrices;

    public function __construct(
        PsnItem $psnItemService,
        PersonalisationPrices $personalisationPrices,
        PersonalisationHelper $dataHelper
    ) {
        $this->psnItemService = $psnItemService;
        $this->personalisationPrices = $personalisationPrices;
        $this->dataHelper = $dataHelper;
    }

    public function execute(Observer $observer): void
    {
        if ($this->dataHelper->isEnabled()) {
            $cartItem = $observer->getEvent()->getSourceItem();
            $addedItem = $observer->getEvent()->getAddedItem();

            $itemId = (int) $cartItem->getItemId();
            foreach ($cartItem->getChildren() as $child) {
                $itemId = (int) $child->getId();
                break;
            }
            $psnItem = $this->psnItemService->getItemByQuoteItemId($itemId);
            if ($psnItem !== null) {
                $store = $addedItem->getQuote()->getStoreId();
                $personalisationPrice = $this->personalisationPrices->getFinalPrice($store);
                if ($personalisationPrice) {
                    $customPrice = $addedItem->getPrice() + $personalisationPrice;
                    $addedItem->setCustomPrice($customPrice);
                    $addedItem->setOriginalCustomPrice($customPrice);
                    $addedItem->getProduct()->setIsSuperMode(true);
                }
                $addedItem->setData('psn_item_id', $psnItem->getData('entity_id'));
            }
        }
    }
}
