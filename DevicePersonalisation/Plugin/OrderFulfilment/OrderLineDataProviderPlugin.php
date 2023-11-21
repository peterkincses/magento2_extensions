<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\OrderFulfilment;

use BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface;
use BAT\DevicePersonalisation\Helper\Data;
use BAT\DevicePersonalisation\Model\Service\PsnItem;
use BAT\OrderFulfilment\Model\Api\Request\OrderLineDataProvider;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Item;

class OrderLineDataProviderPlugin
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var PsnItem
     */
    private $psnItemService;

    public function __construct(
        Data $helper,
        PsnItem $psnItemService
    ) {
        $this->helper = $helper;
        $this->psnItemService = $psnItemService;
    }

    public function afterGetOrderLineData(OrderLineDataProvider $subject, array $result, Order $order, Item $item): array
    {
        $storeCode = $order->getStore()->getCode();
        if (!$this->helper->isEnabled($storeCode)) {
            return $result;
        }

        $children = $item->getChildrenItems();
        if (strtolower($item->getProductType()) == 'configurable' && count($children)) {
            foreach ($children as $child) {
                $psnItem = $this->psnItemService->getItemByQuoteItemId((int) $child->getQuoteItemId());
                if (!empty($psnItem)) {
                    $result['personalisation'] = $this->getPersonalisationData($psnItem);
                    break;
                }
            }
        } else {
            $psnItem = $this->psnItemService->getItemByQuoteItemId((int) $item->getQuoteItemId());
            if (!empty($psnItem)) {
                $result['personalisation'] = $this->getPersonalisationData($psnItem);
            }
        }

        if (!isset($result['personalisation'])) {
            $result['personalisation'] = $this->getEmptyPersonalisationData();
        }
        return $result;
    }

    private function getPersonalisationData(PsnItemDataInterface $psnItem): array
    {
        return [
            "isPersonalised" => true,
            "frontFont" => $psnItem->getFrontFont(),
            "frontText" => $psnItem->getFrontText(),
            "fontOrientation" => $psnItem->getFrontOrientation(),
            "frontPattern" => $psnItem->getFrontPattern(),
            "frontIcon" => $psnItem->getFrontIcon(),
            "backFont" => $psnItem->getBackFont(),
            "backText" => $psnItem->getBackText(),
            "backOrientation" => $psnItem->getBackOrientation(),
        ];
    }

    private function getEmptyPersonalisationData(): array
    {
        return [
            "isPersonalised" => false,
            "frontFont" => null,
            "frontText" => null,
            "fontOrientation" => null,
            "frontPattern" => null,
            "frontIcon" => null,
            "backFont" => null,
            "backText" => null,
            "backOrientation" => null,
        ];
    }
}
