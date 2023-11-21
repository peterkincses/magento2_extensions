<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\Rule;

use Magento\SalesRule\Model\Rule\Action\Discount\AbstractDiscount;
use BAT\DevicePersonalisation\Model\Service\PsnItem;
use BAT\DevicePersonalisation\Helper\Data;

class DiscountActionPlugin
{
    /**
     * @var PsnItem
     */
    protected $psnItem;

    /**
     * @var Data
     */
    protected $helper;

    public function __construct(
        PsnItem $psnItem,
        Data $helper
    ) {
        $this->psnItem = $psnItem;
        $this->helper = $helper;
    }

    /**
     * {@inheritDoc}
     */
    public function beforeCalculate(AbstractDiscount $subject, $rule, $item, $qty)
    {
        if (
            !$this->helper->isEnabled()
            || !$rule->getData('psn_exclude_from_discount')
            || !($rule->getData('simple_action') == 'buyxgetn_perc')
        ) {
            return [$rule, $item, $qty];
        }
        $checkItem = $item;
        if ($item->getChildren()) {
            $checkItem = $item->getChildren()[0];
        }
        $id = (int) $checkItem->getId();
        $psnItem = $this->psnItem->getItemByQuoteItemId($id);
        if (!$psnItem) {
            return [$rule, $item, $qty];
        }
        $fullPsnPrice = $psnItem->getData('personalisation_price') + $psnItem->getData('personalisation_tax');
        $item->setOrigDiscountCalculationPrice($item->getDiscountCalculationPrice());
        $item->setOrigBaseDiscountCalculationPrice($item->getBaseDiscountCalculationPrice());
        $item->setDiscountCalculationPrice($item->getDiscountCalculationPrice() - $fullPsnPrice);
        $item->setBaseDiscountCalculationPrice($item->getBaseDiscountCalculationPrice() - $fullPsnPrice);
        return [$rule, $item, $qty];
    }
}
