<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel\PsnFonts\Grid;

use BAT\DevicePersonalisation\Api\Data\PsnFontsInterface;
use BAT\DevicePersonalisation\Helper\Data;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnFonts as PsnFontsResource;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnFonts\Collection as PsnFontsCollection;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;
use Psr\Log\LoggerInterface;
use Zend_Db_Expr;

class Collection extends PsnFontsCollection implements SearchResultInterface
{
    /**
     * @var AggregationInterface
     */
    protected $aggregations;

    /**
     * @var Data
     */
    protected $helper;

    public function __construct(
        Data $helper,
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
        $this->helper = $helper;
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

    protected function _initSelect(): void
    {
        parent::_initSelect();

        $storeId = $this->helper->getRequestStore();

        $this->getSelect()->joinLeft(
            ['override_table' => $this->getTable(PsnFontsResource::TABLE_OVERRIDES)],
            'main_table.font_id = override_table.font_id and override_table.store_id = ' . $storeId,
            []
        )->columns(new Zend_Db_Expr('IF(override_table.font_id is null or override_table.name is null, main_table.name, override_table.name) as custom_name'))
        ->columns(new Zend_Db_Expr('IF(override_table.font_id is null or override_table.status is null, main_table.status, override_table.status) as custom_status'))
        ->columns(new Zend_Db_Expr('IF(override_table.font_id is null or override_table.preview_text is null, main_table.preview_text, override_table.preview_text) as preview_text'))
        ->columns(new Zend_Db_Expr('IF(override_table.font_id is null or override_table.font_size is null, main_table.font_size, override_table.font_size) as font_size'));

        $this->addFilterToMap(
            'custom_name',
            new Zend_Db_Expr('IF(override_table.font_id is null or override_table.name is null, main_table.name, override_table.name)')
        );
        $this->addFilterToMap(
            'custom_status',
            new Zend_Db_Expr('IF(override_table.font_id is null or override_table.status is null, main_table.status, override_table.status)')
        );
        $this->addFilterToMap(
            'preview_text',
            new Zend_Db_Expr('IF(override_table.font_id is null or override_table.preview_text is null, main_table.preview_text, override_table.preview_text)')
        );
        $this->addFilterToMap(
            'font_size',
            new Zend_Db_Expr('IF(override_table.font_id is null or override_table.font_size is null, main_table.font_size, override_table.font_size)')
        );
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
}
