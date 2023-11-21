<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model;

use BAT\DevicePersonalisation\Api\Data;
use BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface;
use BAT\DevicePersonalisation\Api\Data\PsnItemDataInterfaceFactory;
use BAT\DevicePersonalisation\Api\Data\PsnItemSearchResultsInterface;
use BAT\DevicePersonalisation\Api\Data\PsnItemSearchResultsInterfaceFactory;
use BAT\DevicePersonalisation\Api\PsnItemRepositoryInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnItem as ResourcePsnItem;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnItem\CollectionFactory as PsnItemCollectionFactory;
use Exception;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class PsnItemRepository implements PsnItemRepositoryInterface
{
    /**
     * @var ResourcePsnItem
     */
    private $resource;

    /**
     * @var PsnItemFactory
     */
    private $psnItemFactory;

    /**
     * @var PsnItemCollectionFactory
     */
    private $psnItemCollectionFactory;

    /**
     * @var PsnItemSearchResultsInterfaceFactory
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
     * @var PsnItemDataInterfaceFactory
     */
    private $dataItemFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        ResourcePsnItem $resource,
        PsnItemFactory $psnItemFactory,
        PsnItemDataInterface $dataItemFactory,
        PsnItemCollectionFactory $psnItemCollectionFactory,
        PsnItemSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->psnItemFactory = $psnItemFactory;
        $this->psnItemCollectionFactory = $psnItemCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataItemFactory = $dataItemFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    public function save(PsnItemDataInterface $PsnItem): PsnItemDataInterface
    {
        try {
            $this->resource->save($PsnItem);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $PsnItem;
    }

    public function getById(int $PsnItemId): PsnItemDataInterface
    {
        $PsnItem = $this->psnItemFactory->create();
        $this->resource->load($PsnItem, $PsnItemId);
        if (!$PsnItem->getId()) {
            throw new NoSuchEntityException(__('The Item with the "%1" ID doesn\'t exist.', $PsnItemId));
        }
        return $PsnItem;
    }

    public function getList(SearchCriteriaInterface $criteria): PsnItemSearchResultsInterface
    {
        /** @var \BAT\DevicePersonalisation\Model\ResourceModel\PsnItem\Collection $collection */
        $collection = $this->psnItemCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var PsnItemSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    public function delete(PsnItemDataInterface $psnItem): bool
    {
        try {
            $this->resource->delete($psnItem);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function deleteById(int $psnItemId): bool
    {
        return $this->delete($this->getById($psnItemId));
    }
}
