<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model;

use BAT\DevicePersonalisation\Api\Data;
use BAT\DevicePersonalisation\Api\Data\PsnIconDataInterface;
use BAT\DevicePersonalisation\Api\Data\PsnIconDataInterfaceFactory;
use BAT\DevicePersonalisation\Api\Data\PsnIconSearchResultsInterface;
use BAT\DevicePersonalisation\Api\Data\PsnIconSearchResultsInterfaceFactory;
use BAT\DevicePersonalisation\Api\PsnIconRepositoryInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnIcon as ResourcePsnIcon;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnIcon\CollectionFactory as PsnIconCollectionFactory;
use Exception;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class PsnIconRepository implements PsnIconRepositoryInterface
{
    /**
     * @var ResourcePsnIcon
     */
    private $resource;

    /**
     * @var PsnIconFactory
     */
    private $psnIconFactory;

    /**
     * @var PsnIconCollectionFactory
     */
    private $psnIconCollectionFactory;

    /**
     * @var PsnIconSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    private $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    private $dataObjectProcessor;

    /**
     * @var PsnIconDataInterfaceFactory
     */
    private $dataIconFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        ResourcePsnIcon $resource,
        PsnIconFactory $psnIconFactory,
        PsnIconDataInterface $dataIconFactory,
        PsnIconCollectionFactory $psnIconCollectionFactory,
        PsnIconSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->psnIconFactory = $psnIconFactory;
        $this->psnIconCollectionFactory = $psnIconCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataIconFactory = $dataIconFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function save(PsnIconDataInterface $PsnIcon): PsnIconDataInterface
    {
        try {
            $this->resource->save($PsnIcon);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $PsnIcon;
    }

    public function getById(int $PsnIconId): PsnIconDataInterface
    {
        $PsnIcon = $this->psnIconFactory->create();
        $this->resource->load($PsnIcon, $PsnIconId);
        if (!$PsnIcon->getId()) {
            throw new NoSuchEntityException(__('The Icon with the "%1" ID doesn\'t exist.', $PsnIconId));
        }
        return $PsnIcon;
    }

    public function getList(SearchCriteriaInterface $criteria): PsnIconSearchResultsInterface
    {
        $collection = $this->psnIconCollectionFactory->create();
        $collection = $collection->joinIconsOverride();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    public function delete(PsnIconDataInterface $psnIcon): bool
    {
        try {
            $this->resource->delete($psnIcon);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function deleteById(int $psnIconId): bool
    {
        return $this->delete($this->getById($psnIconId));
    }

    public function getIconsByStoreId(int $storeId, int $iconId): array
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTable('psn_icons_overrides');
        $query = $connection->select()->from(['cbs' => $table], '*') . ' where icon_id= ' . (int) $iconId . ' AND store_id=' . (int) $storeId;
        $records  = $connection->fetchRow($query);
        if (!$records) {
            return [];
        }
        return $records;
    }
}
