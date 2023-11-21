<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Observer\Quote;

use BAT\DevicePersonalisation\Model\Service\PsnItem as PsnItemService;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use BAT\DevicePersonalisation\Helper\Data as PersonalisationHelper;

class MergeBeforeObserver implements ObserverInterface
{
    /**
     * @var PsnItemService
     */
    protected $psnItemService;

    /**
     * @var PersonalisationHelper
     */
    private $dataHelper;

    public function __construct(
        PsnItemService $psnItemService,
        PersonalisationHelper $dataHelper
    ) {
        $this->psnItemService = $psnItemService;
        $this->dataHelper = $dataHelper;
    }

    public function execute(Observer $observer): void
    {
        if ($this->dataHelper->isEnabled()) {
            $initialQuote = $observer->getEvent()->getData('source');
            foreach ($initialQuote->getAllItems() as $item) {
                $itemId = (int) $item->getId();
                $psnItem = $this->psnItemService->getItemByQuoteItemId($itemId);
                if ($psnItem !== null) {
                    $additionalData = $item->getAdditionalData();
                    $additionalData .= '<<item_id=' . $itemId . '>>';
                    $item->setAdditionalData($additionalData);
                }
            }
        }
    }
}
