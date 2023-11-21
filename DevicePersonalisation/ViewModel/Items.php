<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\ViewModel;

use BAT\DevicePersonalisation\Model\Service\PsnItem;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order\Creditmemo\Item as CreditmemoItem;
use Magento\Sales\Model\Order\Invoice\Item as InvoiceItem;
use Magento\Sales\Model\Order\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;
use BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface;

class Items implements ArgumentInterface
{
    /**
     * @var PsnItem
     */
    private $psnItem;

    public function __construct(
        PsnItem $psnItem
    ) {
        $this->psnItem = $psnItem;
    }

    /**
     * @param QuoteItem|Item|InvoiceItem|CreditmemoItem $item
     * @return array
     */
    public function getPersonalisationInfo(Item $item): array
    {
        $psnItem = [];
        $orderItemData = $item->getData();
        $orderItemId = isset($orderItemData['order_item_id']) ? $orderItemData['order_item_id'] : $orderItemData['item_id'];
        $orderItemId = (int) $orderItemId;
        if ($item->getProducttype() == "configurable") {
            foreach ($item->getChildrenItems() as $child) {
                $orderItemId = (int) $child->getId();
            }
        }
        if ($orderItemId) {
            $psnItem =  $this->psnItem->getItemByOrderItemId($orderItemId);
            $psnItem = !empty($psnItem) ? $psnItem->getData() : [];
        }
        return $psnItem;
    }

    public function getFormated(string $value): string
    {
        if ($value == "") {
            return "N/A";
        }
        return ucfirst($value);
    }
}
