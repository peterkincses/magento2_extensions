<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Service;

use BAT\DevicePersonalisation\Model\ResourceModel\PsnItem\Collection as PsnItemCollection;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnItem\CollectionFactory as PsnItemCollectionFactory;
use Exception;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;

class OrderHasPsnItem
{

    /**
     * @var PsnItemCollection
     */
    protected $psnItemCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        PsnItemCollectionFactory $psnItemCollectionFactory,
        LoggerInterface $logger
    ) {
        $this->psnItemCollectionFactory = $psnItemCollectionFactory;
        $this->logger = $logger;
    }

    public function hasOrderPsnItem(Order $order): bool
    {
        $result = false;
        if (!$order->getId()) {
            return $result;
        }

        $items = $order->getAllItems();
        if (count($items) == '0') {
            return $result;
        }

        $quoteItemIds = array_map(function ($item) {
            return $item->getQuoteItemId();
        }, $items);

        if (!count($quoteItemIds)) {
            return $result;
        }

        try {
            /** @var PsnItemCollection $orderPsnItemCollection */
            $orderPsnItemCollection = $this->psnItemCollectionFactory->create();
            $orderPsnItemCollection->addFieldToFilter('quote_item_id', ['in' => $quoteItemIds]);
            if ($orderPsnItemCollection->getSize() > 0) {
                $result = true;
            }
        } catch (Exception $e) {
            $this->logger->log('PSN hasOrderPsnItem' . $e->getMessage());
        }
        return $result;
    }
}
