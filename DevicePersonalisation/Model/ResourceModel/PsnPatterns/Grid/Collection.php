<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel\PsnPatterns\Grid;

use BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface;
use BAT\DevicePersonalisation\Helper\Data;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnPatterns\Collection as PsnPatternsCollection;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Search\AggregationInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\View\Element\UiComponent\DataProvider\Document;

/**
 * Collection for displaying grid of patterns
 */
class Collection extends PsnPatternsCollection implements SearchResultInterface
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

    /**
     * {@inheritDoc}
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $storeId = $this->helper->getRequestStore();

        $this->getSelect()->joinLeft(
            ['override_table' => $this->getTable(PsnPatternsInterface::TABLE_OVERRIDES)],
            'main_table.pattern_id = override_table.pattern_id and override_table.store_id = ' . $storeId,
            [
                'name_localized' => 'IF(override_table.pattern_id is null or override_table.name is null, main_table.name, override_table.name)',
                'category_name',
                'status' => 'IF(override_table.pattern_id is null or override_table.status is null, main_table.status, override_table.status)',
            ]
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
