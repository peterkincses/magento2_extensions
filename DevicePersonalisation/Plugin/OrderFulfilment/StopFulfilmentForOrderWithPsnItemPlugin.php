<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\OrderFulfilment;

use BAT\DevicePersonalisation\Model\Service\PsnItem as PsnItemService;
use BAT\OrderFulfilment\Api\OrderValidationServiceInterface;
use Magento\Sales\Model\Order;
use BAT\DevicePersonalisation\Helper\Data as PersonalisationHelper;

class StopFulfilmentForOrderWithPsnItemPlugin
{
    /**
     * @var PsnItemService
     */
    private $psnItemService;

    /**
     * @var PersonalisationHelper
     */
    private $dataHelper;

    public function __construct(
        PsnItemService $psnItemService,
        PersonalisationHelper $helpData
    ) {
        $this->psnItemService = $psnItemService;
        $this->dataHelper = $helpData;
    }

    public function afterCanFulfil(OrderValidationServiceInterface $subject, bool $result, Order $order): bool
    {
        // if order won't be fulfilled anyway no point doing anything
        if (!$result) {
            return $result;
        }

        $orderStoreCode = $order->getStore()->getCode();

        // return early if the module is disabled or we don't prevent fulfilment
        if (!($this->dataHelper->isEnabled($orderStoreCode) && $this->dataHelper->isFulfilmentPrevented($orderStoreCode))) {
            return $result;
        }

        // return false if any item is personalised, otherwise return original result
        foreach ($order->getItems() as $item) {
            if ($this->psnItemService->getItemByQuoteItemId((int) $item->getQuoteItemId())) {
                return false;
            }
        }
        return $result;
    }
}
