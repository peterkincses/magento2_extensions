<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin;

use BAT\DevicePersonalisation\Model\Service\PsnItem;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderItemInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use BAT\DevicePersonalisation\Helper\Data;

class OrderDataPlugin
{
    public const FIELD_NAME = 'personalisation';

    /**
     * @var OrderExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @var OrderItemInterfaceFactory
     */
    protected $orderItemExtension;

    /**
     * @var PsnItem
     */
    protected $psnItem;

    /**
     * @var Data
     */
    protected $helper;

    public function __construct(
        OrderExtensionFactory $extensionFactory,
        OrderItemInterfaceFactory $orderItemExtension,
        PsnItem $psnItem,
        Data $helper
    ) {
        $this->extensionFactory = $extensionFactory;
        $this->orderItemExtension = $orderItemExtension;
        $this->psnItem = $psnItem;
        $this->helper = $helper;
    }

    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order): OrderInterface
    {
        $storeCode = $order->getStore()->getCode();
        if (!$this->helper->isEnabled($storeCode)) {
            return $order;
        }
        foreach ($order->getItems() as $item) {
            $parentItem = "";
            $children = $item->getChildrenItems();
            if (strtolower($item->getProductType()) == 'configurable' && count($children)) {
                foreach ($children as $child) {
                    $psnItem = $this->psnItem->getItemByQuoteItemId((int) $child->getQuoteItemId());
                    if (!empty($psnItem)) {
                        $parentItem = $child;
                    }
                }
            } else {
                $parentItem = $item;
            }

            if ($parentItem) {
                $extensionAttributes = $parentItem->getExtensionAttributes();
                $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
                $personalisation = $this->psnItem->getItemByOrderItemId((int) $parentItem->getItemId());
                if ($personalisation && $personalisation->getId()) {
                    $extensionAttributes->setData(self::FIELD_NAME, 1);
                    $parentItem->setExtensionAttributes($extensionAttributes);
                } else {
                    $extensionAttributes->setData(self::FIELD_NAME, 0);
                    $parentItem->setExtensionAttributes($extensionAttributes);
                }
            }
        }

        return $order;
    }
}
