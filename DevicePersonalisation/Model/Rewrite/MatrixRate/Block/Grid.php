<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Rewrite\MatrixRate\Block;

use Magento\Backend\Block\Widget\Grid\Extended;
use Mageside\ShippingMatrixRates\Block\Adminhtml\Carrier\Matrixrates\Grid as GridBase;

class Grid extends GridBase
{
    /**
     * {@inheritDoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'dest_country',
            ['header' => __('Country'), 'index' => 'dest_country', 'default' => '*']
        );

        $this->addColumn(
            'dest_region',
            ['header' => __('Region/State'), 'index' => 'dest_region', 'default' => '*']
        );

        $this->addColumn(
            'dest_city',
            ['header' => __('City'), 'index' => 'dest_city', 'default' => '*']
        );

        $this->addColumn(
            'dest_zip_from',
            ['header' => __('Zip/Postal Code From'), 'index' => 'dest_zip_from', 'default' => '*']
        );

        $this->addColumn(
            'dest_zip_to',
            ['header' => __('Zip/Postal Code To'), 'index' => 'dest_zip_to', 'default' => '*']
        );

        $this->addColumn(
            'weight_from',
            ['header' => __('Weight From'), 'index' => 'weight_from', 'default' => '*']
        );

        $this->addColumn(
            'weight_to',
            ['header' => __('Weight To'), 'index' => 'weight_to', 'default' => '*']
        );

        $this->addColumn(
            'qty_from',
            ['header' => __('Qty From'), 'index' => 'qty_from', 'default' => '*']
        );

        $this->addColumn(
            'qty_to',
            ['header' => __('Qty To'), 'index' => 'qty_to', 'default' => '*']
        );

        $this->addColumn(
            'price_from',
            ['header' => __('Price From'), 'index' => 'price_from', 'default' => '*']
        );

        $this->addColumn(
            'price_to',
            ['header' => __('Price To'), 'index' => 'price_to', 'default' => '*']
        );

        $this->addColumn(
            'shipping_group',
            ['header' => __('Shipping Group'), 'index' => 'shipping_group', 'default' => '*']
        );

        $this->addColumn(
            'customer_group',
            ['header' => __('Customer Group'), 'index' => 'customer_group', 'default' => '*']
        );

        $this->addColumn(
            'calc_logic',
            ['header' => __('Advanced Calculations'), 'index' => 'calc_logic', 'default' => '*']
        );

        $this->addColumn('price', ['header' => __('Shipping Price'), 'index' => 'price']);

        $this->addColumn('cost', ['header' => __('Cost'), 'index' => 'cost']);

        $this->addColumn(
            'delivery_method',
            ['header' => __('Delivery Method Name'), 'index' => 'delivery_method']
        );

        $this->addColumn('notes', ['header' => __('Notes'), 'index' => 'notes']);

        $this->addColumn('exclude_personalisation', ['header' => __('Exclude Personalisation'), 'index' => 'exclude_personalisation']);

        return Extended::_prepareColumns();
    }
}
