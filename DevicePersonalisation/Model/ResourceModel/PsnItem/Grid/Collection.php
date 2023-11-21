<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel\PsnItem\Grid;

use BAT\DevicePersonalisation\Model\ResourceModel\PsnItem\Collection as PsnItemCollection;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface as AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

class Collection extends PsnItemCollection implements SearchResultInterface
{
    public function __construct(
        RequestInterface $request,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        string $mainTable,
        string $eventPrefix,
        string $eventObject,
        string $resourceModel,
        string $model = Document::class,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->request = $request;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * {@inheritDoc}
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * {@inheritDoc}
     */
    public function setAggregations($aggregations)
    {
        $this->aggregations = $aggregations;
    }

    /**
     * {@inheritDoc}
     */
    public function getSearchCriteria()
    {
        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function setSearchCriteria(SearchCriteriaInterface $searchCriteria = null)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * {@inheritDoc}
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    protected function _initSelect()
    {
        $orderItemTable = $this->getTable('sales_order_item');
        $salesOrderAddress = $this->getTable('sales_order_address');
        $salesShipment = $this->getTable('sales_shipment');
        $salesInvoice = $this->getTable('sales_invoice');
        $customerVarchar = $this->getTable('customer_entity_varchar');
        $salesOrder = $this->getTable('sales_order');
        $mainTable = $this->getMainTable();

        $subquery = new Zend_Db_Expr('(select DISTINCT order_id from psn_item psn_itm_tb inner join sales_order_item custom_item on psn_itm_tb.order_item_id = custom_item.item_id)');

        $this->getSelect()
            ->from(['order' => $salesOrder])
            ->join(
                ['subquery' => $subquery],
                'order.entity_id = subquery.order_id AND order.status != "canceled"',
                []
            )->joinLeft(
                ['item' => $orderItemTable],
                "order.entity_id = item.order_id AND item.product_type in ('simple', 'grouped')",
                []
            )->joinLeft(
                ['main_table' => $mainTable],
                'main_table.order_item_id = item.item_id',
                []
            )->joinLeft(
                ['item2' => $orderItemTable],
                '(item.parent_item_id = item2.item_id AND item2.parent_item_id IS NULL)',
                []
            )->joinLeft(
                ['catalog_product' => $this->getTable('catalog_product_entity')],
                'item.product_id = catalog_product.entity_id',
                []
            )->joinLeft(
                ['billing_address' => $salesOrderAddress],
                'billing_address.parent_id = order.entity_id AND billing_address.address_type = "billing"',
                []
            )->joinLeft(
                ['shipping_address' => $salesOrderAddress],
                'shipping_address.parent_id = order.entity_id AND shipping_address.address_type = "shipping"',
                []
            )->joinLeft(
                ['shipment' => $salesShipment],
                'shipment.order_id = order.entity_id',
                []
            )->columns($this->getColumns());

        $this->addFilterToMap('increment_id', 'order.increment_id');
        $this->addFilterToMap('order_status', 'order.status');
        $this->addFilterToMap('order_id', 'order.entity_id');
        $this->addFilterToMap('order_date', 'order.created_at');
        $this->addFilterToMap('product_name', 'item.name');
        $this->addFilterToMap('sku', new Zend_Db_Expr('IFNULL(item.sku, catalog_product.sku)'));
        $this->addFilterToMap('customer_email', 'order.customer_email');
        $this->addFilterToMap('customer_name', new Zend_Db_Expr('IFNULL(CONCAT(order.customer_firstname, " ", order.customer_lastname), '
        . 'CONCAT(billing_address.firstname, " ", billing_address.lastname))'));
        $this->addFilterToMap('shipping_date', 'shipment.created_at');
        $this->addFilterToMap('item_price', new Zend_Db_Expr('IF(item.base_price = 0, item2.base_price, item.base_price)'));
        $this->addFilterToMap('original_item_price', new Zend_Db_Expr('IF(IFNULL(item.base_original_price, item2.base_original_price) > 0, '
        . 'ROUND(COALESCE(IF(item.original_price = 0, item2.original_price, item.original_price), 0) '
        . '- (item.tax_percent * COALESCE(IFNULL(item.original_price, item2.original_price), 0) / (100 + item.tax_percent)),2), '
        . 'COALESCE(IF(item.base_price = 0, item2.base_price, item.base_price), 0))'));
        $this->addFilterToMap('original_price', new Zend_Db_Expr('IFNULL(item.base_price_incl_tax, item2.base_price_incl_tax)'));
        $this->addFilterToMap('final_price', new Zend_Db_Expr('IF(IFNULL(item.base_original_price, item2.base_original_price) > 0, '
        . 'COALESCE(IF(item.original_price = 0, item2.original_price, item.original_price), 0), '
        . 'COALESCE(IFNULL(item.base_price_incl_tax, item2.base_price_incl_tax), 0))'));
        $this->addFilterToMap('final_subtotal', new Zend_Db_Expr('IF(IFNULL(item.base_original_price, item2.base_original_price) > 0, '
        . 'ROUND((COALESCE(IFNULL(item.base_original_price, item2.base_original_price), 0) '
        . '- (item.tax_percent * COALESCE(IFNULL(item.base_original_price, item2.base_original_price), 0) / (100 + item.tax_percent))),2) '
        . '* (IFNULL(item.qty_ordered, item2.qty_ordered)), COALESCE((IFNULL(item.qty_ordered, item2.qty_ordered) '
        . '* IFNULL(item.base_price, item2.base_price)), 0))'));
        $this->addFilterToMap('shipping_amount', 'order.shipping_amount');
        $this->addFilterToMap('discount', 'item.base_discount_amount');
        $this->addFilterToMap('qty', 'item.qty_ordered');
        $this->addFilterToMap('store_id', 'order.store_id');
        $this->addFilterToMap('base_currency_code', 'order.base_currency_code');
        $this->addFilterToMap('entity_id', 'item.item_id');
        $this->addFilterToMap('front_font', 'main_table.front_font');
        $this->addFilterToMap('front_text', 'main_table.front_text');
        $this->addFilterToMap('front_orientation', 'main_table.front_orientation');
        $this->addFilterToMap('front_pattern', 'main_table.front_pattern');
        $this->addFilterToMap('front_icon', 'main_table.front_icon');
        $this->addFilterToMap('back_font', 'main_table.back_font');
        $this->addFilterToMap('back_text', 'main_table.back_text');
        $this->addFilterToMap('back_orientation', 'main_table.back_orientation');
        $this->addFilterToMap('personalisation_price', 'main_table.personalisation_price');
        $this->addFilterToMap('shipping_description', 'order.shipping_description');
        $this->addFilterToMap('order_item_id', new Zend_Db_Expr('IFNULL(item2.item_id, item.item_id)'));
        $this->addFilterToMap('country', new Zend_Db_Expr('COALESCE(shipping_address.country_id, billing_address.country_id, "")'));
        $this->addFilterToMap('region', new Zend_Db_Expr('COALESCE(shipping_address.region, billing_address.region, "")'));
        $this->addFilterToMap('vat_id', new Zend_Db_Expr('COALESCE(shipping_address.vat_id, billing_address.vat_id, "")'));
        $this->addFilterToMap('city', new Zend_Db_Expr('COALESCE(shipping_address.city, billing_address.city, "")'));
        $this->addFilterToMap('postcode', new Zend_Db_Expr('COALESCE(shipping_address.postcode, billing_address.postcode, "")'));
        $this->addFilterToMap('address', new Zend_Db_Expr('COALESCE(shipping_address.street, billing_address.street, "")'));
        $this->addFilterToMap('telephone', new Zend_Db_Expr('COALESCE(shipping_address.telephone, billing_address.telephone, "")'));
        return $this;
    }

    private function getColumns(): array
    {
        return [
            'increment_id'      => 'order.increment_id',
            'sku'               => new Zend_Db_Expr('IFNULL(item.sku, catalog_product.sku)'),
            'customer_name'     => new Zend_Db_Expr('IFNULL(CONCAT(order.customer_firstname, " ", order.customer_lastname), '
            . 'CONCAT(billing_address.firstname, " ", billing_address.lastname))'),
            'order_status'      => 'order.status',
            'store_id'          => 'order.store_id',
            'order_id'          => 'order.entity_id',
            'order_date'        => 'order.created_at',
            'product_id'        => 'IFNULL(item.product_id, item2.product_id)',
            'product_name'      => 'item.name',
            'customer_id'       => 'order.customer_id',
            'customer_email'    => 'order.customer_email',
            'shipping_date'     => 'shipment.created_at',
            'item_price'        => new Zend_Db_Expr('IF(item.base_price = 0, item2.base_price, item.base_price)'),
            'original_item_price' => new Zend_Db_Expr('IF(IFNULL(item.base_original_price, item2.base_original_price) > 0, '
            . 'ROUND(COALESCE(IF(item.original_price = 0, item2.original_price, item.original_price), 0) '
            . '- (item.tax_percent * COALESCE(IFNULL(item.original_price, item2.original_price), 0) / (100 + item.tax_percent)),2), '
            . 'COALESCE(IF(item.base_price = 0, item2.base_price, item.base_price), 0))'),
            'original_price'    => new Zend_Db_Expr('IFNULL(item.base_price_incl_tax, item2.base_price_incl_tax)'),
            'final_price'       => new Zend_Db_Expr('IF(IFNULL(item.base_original_price, item2.base_original_price) > 0, '
            . 'COALESCE(IF(item.original_price = 0, item2.original_price, item.original_price), 0), '
            . 'COALESCE(IFNULL(item.base_price_incl_tax, item2.base_price_incl_tax), 0))'),
            'final_subtotal'    => new Zend_Db_Expr('IF(IFNULL(item.base_original_price, item2.base_original_price) > 0, '
            . 'ROUND((COALESCE(IFNULL(item.base_original_price, item2.base_original_price), 0) '
            . '- (item.tax_percent * COALESCE(IFNULL(item.base_original_price, item2.base_original_price), 0) / (100 + item.tax_percent))),2) '
            . '* (IFNULL(item.qty_ordered, item2.qty_ordered)), COALESCE((IFNULL(item.qty_ordered, item2.qty_ordered) '
            . '* IFNULL(item.base_price, item2.base_price)), 0))'),
            'shipping_amount'   => 'order.shipping_amount',
            'discount'          => 'item.base_discount_amount',
            'qty'               => 'item.qty_ordered',
            'base_currency_code' => 'order.base_currency_code',
            'front_font'         => 'main_table.front_font',
            'front_text'         => 'main_table.front_text',
            'front_orientation'  => 'main_table.front_orientation',
            'front_pattern'      => 'main_table.front_pattern',
            'front_icon'         => 'main_table.front_icon',
            'back_font'         => 'main_table.back_font',
            'back_text'         => 'main_table.back_text',
            'back_orientation'         => 'main_table.back_orientation',
            'personalisation_price'    => 'main_table.personalisation_price',
            'entity_id'             => 'item.item_id',
            'order_item_id'           => new Zend_Db_Expr('IFNULL(item2.item_id, item.item_id)'),
            'country'       => new Zend_Db_Expr('COALESCE(shipping_address.country_id, billing_address.country_id, "")'),
            'region'        => new Zend_Db_Expr('COALESCE(shipping_address.region, billing_address.region, "")'),
            'vat_id'        => new Zend_Db_Expr('COALESCE(shipping_address.vat_id, billing_address.vat_id, "")'),
            'city'          => new Zend_Db_Expr('COALESCE(shipping_address.city, billing_address.city, "")'),
            'postcode'      => new Zend_Db_Expr('COALESCE(shipping_address.postcode, billing_address.postcode, "")'),
            'address'       => new Zend_Db_Expr('COALESCE(shipping_address.street, billing_address.street, "")'),
            'telephone'     => new Zend_Db_Expr('COALESCE(shipping_address.telephone, billing_address.telephone, "")'),
        ];
    }
}
