<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel\PsnReport\Grid;

use BAT\DevicePersonalisation\Helper\Data as PsnHelper;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnItem\Collection as PsnItemCollection;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EavAttribute;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Magento\Store\Api\StoreRepositoryInterface;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

class Collection extends PsnItemCollection implements SearchResultInterface
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;
    /**
     * @var PsnHelper
     */
    private $psnHelper;
    /**
     * @var EavAttribute
     */
    private $eavAttribute;

    public function __construct(
        RequestInterface $request,
        StoreRepositoryInterface $storeRepository,
        PsnHelper $psnHelper,
        EntityFactoryInterface $entityFactory,
        LoggerInterface $logger,
        FetchStrategyInterface $fetchStrategy,
        ManagerInterface $eventManager,
        EavAttribute $eavAttribute,
        string $mainTable,
        string $eventPrefix,
        string $eventObject,
        string $resourceModel,
        string $model = Document::class,
        AdapterInterface $connection = null,
        AbstractDb $resource = null
    ) {
        $this->request = $request;
        $this->storeRepository = $storeRepository;
        $this->psnHelper = $psnHelper;
        $this->eavAttribute = $eavAttribute;
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
        $enabledStoreIds = [];
        $orderItemTable = $this->getTable('sales_order_item');
        $salesOrderAddress = $this->getTable('sales_order_address');
        $salesShipment = $this->getTable('sales_shipment');
        $customerAddressVarchar = $this->getTable('customer_address_entity_varchar');
        $salesOrder = $this->getTable('sales_order');
        $psnItem = $this->getTable('psn_item');
        $salesOrderPayment = $this->getTable('sales_order_payment');

        $stores = $this->storeRepository->getList();

        $filter = $this->request->getParam('filters');

        foreach ($stores as $store) {
            if ($this->psnHelper->isFulfilmentReportEnabled($store->getId())) {
                $enabledStoreIds[] = $store->getId();
            }
        }
        //use restricted parameters instead of loading everything where no filter defined
        $storeId = isset($filter['store_id']) && in_array($filter['store_id'], $enabledStoreIds) ? $filter['store_id'] : 0;
        $dateTo = isset($filter['order_date']['to']) ?  date('Y-m-d', strtotime($filter['order_date']['to'])) : date('Y-m-d', time());
        $dateFrom = isset($filter['order_date']['from']) ?  date('Y-m-d', strtotime($filter['order_date']['from'])) : date('Y-m-d', strtotime('-30 day'));

        //use attribute id instead of subquery
        $attributeIdNeoExt = $this->eavAttribute->getIdByCode('customer_address', 'neo_ext');
        $attributeIdReferencia = $this->eavAttribute->getIdByCode('customer_address', 'referencia');
        $attributeIdNumeroInterior = $this->eavAttribute->getIdByCode('customer_address', 'numero_interior');
        $attributeIdColonia = $this->eavAttribute->getIdByCode('customer_address', 'colonia');

        $this->getSelect()
            ->from(
                ['order' => $salesOrder],
                [
                    'entity_id',
                    'status',
                    'increment_id',
                    'created_at',
                    'customer_email',
                    'customer_firstname',
                    'customer_lastname',
                    'shipping_amount',
                    'store_id',
                    'base_currency_code',
                    'shipping_description',
                    'shipping_incl_tax',
                    'customer_id',
                ]
            )
            ->join(
                ['item' => $orderItemTable],
                "order.entity_id = item.order_id AND item.product_type IN ('simple','grouped') AND order.status != 'canceled'",
                [
                    'order_id',
                    'product_type',
                    'item_id',
                    'parent_item_id',
                    'product_id',
                    'name',
                    'sku',
                    'base_price',
                    'base_original_price',
                    'original_price',
                    'tax_percent',
                    'base_price_incl_tax',
                    'qty_ordered',
                    'base_discount_amount',
                    'row_total_incl_tax',
                    'tax_amount',
                ]
            )->joinLeft(
                ['psn_item' => $psnItem],
                'psn_item.order_item_id = item.item_id',
                []
            )->joinLeft(
                ['payment' => $salesOrderPayment],
                'payment.parent_id = order.entity_id',
                ['method','parent_id']
            )->joinLeft(
                ['item2' => $orderItemTable],
                '(item.parent_item_id = item2.item_id AND item2.parent_item_id IS NULL)',
                [
                    'order_id',
                    'product_type',
                    'item_id',
                    'parent_item_id',
                    'product_id',
                    'name',
                    'sku',
                    'base_price',
                    'base_original_price',
                    'original_price',
                    'tax_percent',
                    'base_price_incl_tax',
                    'qty_ordered',
                    'base_discount_amount',
                    'row_total_incl_tax',
                    'tax_amount',
                ]
            )->joinLeft(
                ['catalog_product' => $this->getTable('catalog_product_entity')],
                'item.product_id = catalog_product.entity_id',
                ['entity_id','sku']
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
                ['order_id','created_at']
            )->joinLeft(
                ['neo_ext' => $customerAddressVarchar],
                'neo_ext.entity_id = shipping_address.customer_address_id AND neo_ext.attribute_id = ' . $attributeIdNeoExt,
                ['value']
            )->joinLeft(
                ['referencia' => $customerAddressVarchar],
                'referencia.entity_id = shipping_address.customer_address_id AND referencia.attribute_id = ' . $attributeIdReferencia,
                ['value']
            )->joinLeft(
                ['numero_interior' => $customerAddressVarchar],
                'numero_interior.entity_id = shipping_address.customer_address_id AND numero_interior.attribute_id = ' . $attributeIdNumeroInterior,
                ['value']
            )->joinLeft(
                ['colonia' => $customerAddressVarchar],
                'colonia.entity_id = shipping_address.customer_address_id AND colonia.attribute_id =' . $attributeIdColonia,
                ['value']
            )->where('order.store_id = ?', $storeId)
            ->where(sprintf('order.created_at between \'%s\' AND \'%s\'', $dateFrom, $dateTo))
            ->columns($this->getColumns());

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
        $this->addFilterToMap('front_font', 'psn_item.front_font');
        $this->addFilterToMap('front_text', 'psn_item.front_text');
        $this->addFilterToMap('front_orientation', 'psn_item.front_orientation');
        $this->addFilterToMap('front_pattern', 'psn_item.front_pattern');
        $this->addFilterToMap('front_icon', 'psn_item.front_icon');
        $this->addFilterToMap('back_font', 'psn_item.back_font');
        $this->addFilterToMap('back_text', 'psn_item.back_text');
        $this->addFilterToMap('back_orientation', 'psn_item.back_orientation');
        $this->addFilterToMap('personalisation_price', 'psn_item.personalisation_price');
        $this->addFilterToMap('shipping_description', 'order.shipping_description');
        $this->addFilterToMap('order_item_id', new Zend_Db_Expr('IFNULL(item2.item_id, item.item_id)'));
        $this->addFilterToMap('shipping_country', 'shipping_address.country_id');
        $this->addFilterToMap('shipping_state', 'shipping_address.region');
        $this->addFilterToMap('shipping_city', 'shipping_address.city');
        $this->addFilterToMap('billing_postcode', 'billing_address.postcode');
        $this->addFilterToMap('billing_address', 'billing_address.street');
        $this->addFilterToMap('payment_method', 'payment.method');
        $this->addFilterToMap('shipping_street', 'shipping_address.street');
        $this->addFilterToMap('billing_telephone', 'billing_address.telephone');
        $this->addFilterToMap('cost_delivery', 'order.shipping_incl_tax');
        $this->addFilterToMap('total_incl_tax', new Zend_Db_Expr('IF(item.row_total_incl_tax = 0, item2.row_total_incl_tax, item.row_total_incl_tax)'));
        $this->addFilterToMap('tax', new Zend_Db_Expr('IF(item.tax_amount = 0, item2.tax_amount, item.tax_amount)'));
        $this->addFilterToMap('engraving', new Zend_Db_Expr('IF(psn_item.order_item_id is null, 0, 1)'));
        $this->addFilterToMap('shipping_numero_exterior', 'neo_ext.value');
        $this->addFilterToMap('shipping_colonia', 'colonia.value');
        $this->addFilterToMap('shipping_numero_interior', 'numero_interior.value');
        $this->addFilterToMap('shipping_referencia', 'referencia.value');

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
            'front_font'         => 'psn_item.front_font',
            'front_text'         => 'psn_item.front_text',
            'front_orientation'  => 'psn_item.front_orientation',
            'front_pattern'      => 'psn_item.front_pattern',
            'front_icon'         => 'psn_item.front_icon',
            'back_font'         => 'psn_item.back_font',
            'back_text'         => 'psn_item.back_text',
            'back_orientation'         => 'psn_item.back_orientation',
            'personalisation_price'    => 'IF(psn_item.personalisation_price is null, 0, psn_item.personalisation_price)',
            'entity_id'             => 'CONCAT(item.item_id, RAND() * 1000)',
            'order_item_id'           => new Zend_Db_Expr('IFNULL(item2.item_id, item.item_id)'),
            'country'       => new Zend_Db_Expr('COALESCE(shipping_address.country_id, billing_address.country_id, "")'),
            'region'        => new Zend_Db_Expr('COALESCE(shipping_address.region, billing_address.region, "")'),
            'vat_id'        => new Zend_Db_Expr('COALESCE(shipping_address.vat_id, billing_address.vat_id, "")'),
            'city'          => new Zend_Db_Expr('COALESCE(shipping_address.city, billing_address.city, "")'),
            'billing_postcode'      => 'billing_address.postcode',
            'billing_telephone'     => 'billing_address.telephone',
            'payment_method' => 'payment.method',
            'billing_address' => 'billing_address.street',
            'parcelshop_id' => '0',
            'shipping_street' => 'shipping_address.street',
            'shipping_numero_exterior' => 'neo_ext.value',
            'shipping_colonia' => 'colonia.value',
            'shipping_numero_interior' => 'numero_interior.value',
            'shipping_referencia' => 'referencia.value',
            'shipping_city' => 'shipping_address.city',
            'shipping_state' => 'shipping_address.region',
            'shipping_zipcode' => 'shipping_address.postcode',
            'shipping_phone_number' => 'shipping_address.telephone',
            'shipping_country' => 'shipping_address.country_id',
            'cost_delivery' => 'order.shipping_incl_tax',
            'total_incl_tax' => new Zend_Db_Expr('IF(item.row_total_incl_tax is null, item2.row_total_incl_tax, item.row_total_incl_tax)'),
            'tax' => new Zend_Db_Expr('IF(item.tax_amount = 0, item2.tax_amount, item.tax_amount)'),
            'engraving' => new Zend_Db_Expr('IF(psn_item.order_item_id is null, 0, 1)'),
        ];
    }
}
