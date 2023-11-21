<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Product;

use BAT\DevicePersonalisation\Model\Service\PsnItem;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;
use BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface;

class Info extends Template
{
    /**
     * @var PsnItem
     */
    private $psnItem;

    /**
     * @param array $data
     */
    public function __construct(
        Context $context,
        PsnItem $psnItem,
        array $data = []
    ) {
        $this->psnItem = $psnItem;
        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getPersonalisationInfo(): array
    {
        $psnItem = [];
        $orderItemId = 0;
        if ($this->getLayout()->getBlock('additional.product.info')) {
            $item = $this->getLayout()->getBlock('additional.product.info')->getItem();
            $orderItemId = (int) $item->getId();
            if ($item->getProducttype() == "configurable") {
                foreach ($item->getChildrenItems() as $child) {
                    $orderItemId = (int) $child->getId();
                }
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
