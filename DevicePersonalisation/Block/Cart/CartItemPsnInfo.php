<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Cart;

use BAT\DevicePersonalisation\Model\Service\PsnItem;
use Magento\Checkout\Block\Cart\Additional\Info as AdditionalBlockInfo;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Quote\Model\Quote\Item\AbstractItem;

class CartItemPsnInfo extends Template
{
    /**
     * @var PsnItem
     */
    protected $psnItem;

    public function __construct(
        Context $context,
        PsnItem $psnItem,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->psnItem = $psnItem;
    }

    public function getPsnInfoData(): array
    {
        $psnData = [];
        $item = $this->getCartItem();
        if (!is_null($item)) {
            $itemId = (int) $item->getData('item_id');

            if ($item->getHasChildren()) {
                foreach ($item->getChildren() as $child) {
                    $itemId = (int) $child->getId();
                    break;
                }
            }
            $psnItemModel = $this->psnItem->getItemByQuoteItemId($itemId);

            if (!is_null($psnItemModel)) {
                $psnData = $psnItemModel->getData();
            }
        }
        return $psnData;
    }

    protected function getCartItem(): ?AbstractItem
    {
        $layout = $this->getLayout();
        $block = $layout->getBlock('additional.product.info');
        if ($block instanceof AdditionalBlockInfo) {
            $item = $block->getItem();
            return $item;
        }

        return null;
    }
}
