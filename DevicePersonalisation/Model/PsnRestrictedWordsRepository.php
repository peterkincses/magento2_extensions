<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model;

use BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface;
use BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsSearchResultsInterface;
use BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsSearchResultsInterfaceFactory;
use BAT\DevicePersonalisation\Api\PsnRestrictedWordsRepositoryInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnRestrictedWords as ResourcePsnRestrictedWords;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnRestrictedWords\CollectionFactory as PsnRestrictedWordsCollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class PsnRestrictedWordsRepository implements PsnRestrictedWordsRepositoryInterface
{
    /**
     * @var ResourcePsnRestrictedWords
     */
    private $resource;

    /**
     * @var PsnRestrictedWordsFactory
     */
    private $psnRestrictedWordsFactory;

    /**
     * @var PsnRestrictedWordsCollectionFactory
     */
    private $psnRestrictedWordsCollectionFactory;

    /**
     * @var PsnRestrictedWordsSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    public function __construct(
        ResourcePsnRestrictedWords $resource,
        PsnRestrictedWordsFactory $psnRestrictedWordsFactory,
        PsnRestrictedWordsCollectionFactory $psnRestrictedWordsCollectionFactory,
        PsnRestrictedWordsSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->psnRestrictedWordsFactory = $psnRestrictedWordsFactory;
        $this->psnRestrictedWordsCollectionFactory = $psnRestrictedWordsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    public function save(PsnRestrictedWordsInterface $psnRestrictedWords): PsnRestrictedWordsInterface
    {
        try {
            $this->resource->save($psnRestrictedWords);
        } catch (Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $psnRestrictedWords;
    }

    public function getById(int $wordId): PsnRestrictedWordsInterface
    {
        $psnRestrictedWords = $this->psnRestrictedWordsFactory->create();
        $this->resource->load($psnRestrictedWords, $wordId);
        if (!$psnRestrictedWords->getId()) {
            throw new NoSuchEntityException(__(
                'The restricted word with the "%1" ID doesn\'t exist.',
                $wordId
            ));
        }
        return $psnRestrictedWords;
    }

    public function getList(SearchCriteriaInterface $criteria): PsnRestrictedWordsSearchResultsInterface
    {
        $collection = $this->psnRestrictedWordsCollectionFactory->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    public function delete(PsnRestrictedWordsInterface $psnRestrictedWords): bool
    {
        try {
            $this->resource->delete($psnRestrictedWords);
        } catch (Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    public function deleteById(int $wordId): bool
    {
        return $this->delete($this->getById($wordId));
    }
}
