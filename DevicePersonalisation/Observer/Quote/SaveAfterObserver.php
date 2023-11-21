<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Observer\Quote;

use BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface;
use BAT\DevicePersonalisation\Api\PsnItemRepositoryInterface;
use BAT\DevicePersonalisation\Model\Service\PsnItem as PsnItemService;
use BAT\DevicePersonalisation\Model\PsnItemFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SaveAfterObserver implements ObserverInterface
{
    /**
     * @var PsnItemRepositoryInterface
     */
    protected $psnItemRepository;

    /**
     * @var PsnItemFactory
     */
    protected $psnItemFactory;

    /**
     * @var PsnItemService
     */
    protected $psnItemService;

    public function __construct(
        PsnItemRepositoryInterface $psnItemRepository,
        PsnItemFactory $psnItemFactory,
        PsnItemService $psnItemService
    ) {
        $this->psnItemRepository = $psnItemRepository;
        $this->psnItemFactory = $psnItemFactory;
        $this->psnItemService = $psnItemService;
    }

    public function execute(Observer $observer): void
    {
        /** @var  Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        foreach ($quote->getAllItems() as $item) {
            if ($item->getId() && $item->getData("psn_data")) {
                $params = $item->getData("psn_data");
                $itemId = (int) $item->getId();
                $psnItem = $this->psnItemService->getItemByQuoteItemId($itemId);
                if ($psnItem) {
                    $this->updateItemData($psnItem, $params);
                    $this->psnItemRepository->save($psnItem);
                } else {
                    $params['quote_item_id'] = $item->getId();
                    $psnItem = $this->psnItemFactory->create();
                    $psnItem->setData($params);
                    $this->psnItemRepository->save($psnItem);
                }
            }
        }
    }

    private function updateItemData(PsnItemDataInterface $psnItem, array $data): void
    {
        foreach ($data as $k => $v) {
            $psnItem->setData($k, $v);
        }
    }
}
