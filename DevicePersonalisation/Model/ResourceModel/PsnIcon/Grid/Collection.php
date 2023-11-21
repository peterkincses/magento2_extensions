<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel\PsnIcon\Grid;

use BAT\DevicePersonalisation\Model\ResourceModel\PsnIcon\Collection as PsnIconCollection;
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

class Collection extends PsnIconCollection implements SearchResultInterface
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
        $params = $this->request->getParams();
        $storeId = 0;
        if (isset($params['store'])) {
            $storeId = $params['store'];
        } elseif (isset($params['store_id'])) {
            $storeId = $params['store_id'];
        }

        $nameQry = 'IF(override_table.icon_id is null or override_table.name is null, main_table.name, override_table.name) AS custom_name';
        $nameMapQry = 'IF(override_table.icon_id is null or override_table.name is null, main_table.name, override_table.name)';

        $statusQry = 'IF(override_table.icon_id is null or override_table.status is null, main_table.status, override_table.status) AS custom_status';
        $statusMapQry = 'IF(override_table.icon_id is null or override_table.status is null, main_table.status, override_table.status)';

        $storeQry = "IF(override_table.store_id is null, '" . $storeId . "', '" . $storeId . "') as store";
        $storeMapQry = "IF(override_table.store_id is null, '" . $storeId . "', '" . $storeId . "')";

        $this->getSelect()->joinLeft(
            ['override_table' => $this->getTable('psn_icons_overrides')],
            'main_table.icon_id= override_table.icon_id and override_table.store_id = ' . $storeId,
            ['store_id']
        )->columns(new Zend_Db_Expr($nameQry))
        ->columns(new Zend_Db_Expr($statusQry))
        ->columns(new Zend_Db_Expr($storeQry));
        $this->addFilterToMap(
            'custom_name',
            new Zend_Db_Expr($nameMapQry)
        );
        $this->addFilterToMap(
            'custom_status',
            new Zend_Db_Expr($statusMapQry)
        );
        $this->addFilterToMap(
            'store',
            $storeMapQry
        );
        $this->addFilterToMap('icon_id', 'main_table.icon_id');
        parent::_initSelect();
        return $this;
    }
}
