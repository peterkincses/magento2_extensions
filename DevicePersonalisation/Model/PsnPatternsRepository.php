<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model;

use BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface;
use BAT\DevicePersonalisation\Api\Data\PsnPatternsSearchResultsInterface;
use BAT\DevicePersonalisation\Api\PsnPatternsRepositoryInterface;
use BAT\DevicePersonalisation\Api\Data\PsnPatternsSearchResultsInterfaceFactory;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnPatterns\CollectionFactory;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnPatterns as PsnPatternsResource;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Exception;

/**
 * Class PsnPatternsRepository
 */
class PsnPatternsRepository implements PsnPatternsRepositoryInterface
{
    /**
     * @var PsnPatternsResource
     */
    private $patternsResource;

    /**
     * @var PsnPatternsFactory
     */
    private $patternsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var PsnPatternsSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        PsnPatternsResource $patternsResource,
        PsnPatternsFactory $patternsFactory,
        CollectionProcessorInterface $collectionProcessor,
        PsnPatternsSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->patternsResource = $patternsResource;
        $this->patternsFactory = $patternsFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function getById($patternId)
    {
        $model = $this->patternsFactory->create();
        $this->patternsResource->load($model, $patternId, "pattern_id");
        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Pattern doesn\'t exist'));
        }
        return $model;
    }

    /**
     * {@inheritDoc}
     */
    public function save(PsnPatternsInterface $model)
    {
        try {
            /** @var PsnPatternsInterface $model */
            $this->patternsResource->save($model);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save pattern: %1', $e->getMessage()));
        }
        return $this->getById($model->getId());
    }

    /**
     * {@inheritDoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria): PsnPatternsSearchResultsInterface
    {
        $collection = $this->collectionFactory->create()->joinPatternsOverride();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * {@inheritDoc}
     */
    public function delete(PsnPatternsInterface $model)
    {
        /** @var PsnPatternsInterface $model */
        try {
            $pattern = $this->getById($model->getId());
            $this->patternsResource->delete($pattern);
        } catch (Exception $e) {
            throw new StateException(__('Unable to remove pattern: %1', $e->getMessage()));
        }
        return true;
    }

    /**
     * {@inheritDoc}
     */
    public function deleteById($patternId): bool
    {
        return $this->delete($this->getById($patternId));
    }

    /**
     * {@inheritDoc}
     */
    public function saveStoreData($pattern, $storeData)
    {
        $connection = $this->patternsResource->getConnection();
        $storeId = $storeData["store_id"];
        $existingStoreData = $pattern->getStoreData($storeId);
        if ($existingStoreData) {
            $this->patternsResource->getConnection()->update(
                $connection->getTableName(PsnPatternsInterface::TABLE_OVERRIDES),
                ['name' => $storeData['name'], 'category_name' => $storeData['category_name'], 'status' => $storeData['status']],
                ['pattern_id=?' => $pattern->getId(), 'store_id=?' => $storeId]
            );
        } else {
            $this->patternsResource->getConnection()->insert(
                $connection->getTableName(PsnPatternsInterface::TABLE_OVERRIDES),
                [
                    'pattern_id' => $pattern->getId(), 'store_id' => $storeId,
                    'name' => $storeData["name"], 'category_name' => $storeData['category_name'],
                    'status' => $storeData["status"],
                ]
            );
        }
    }

    /**
     * {@inheritDoc}
     */
    public function removeStoreData($patternId, $storeId)
    {
        $connection = $this->patternsResource->getConnection();
        $this->patternsResource->getConnection()->delete(
            $connection->getTableName(PsnPatternsInterface::TABLE_OVERRIDES),
            ['pattern_id=?' => $patternId, 'store_id=?' => $storeId]
        );
    }
}
