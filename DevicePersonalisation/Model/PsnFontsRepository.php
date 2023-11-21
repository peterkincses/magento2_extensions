<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model;

use BAT\DevicePersonalisation\Api\Data\PsnFontsInterface;
use BAT\DevicePersonalisation\Api\Data\PsnFontsSearchResultsInterface;
use BAT\DevicePersonalisation\Api\Data\PsnFontsSearchResultsInterfaceFactory;
use BAT\DevicePersonalisation\Api\PsnFontsRepositoryInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnFonts as PsnFontsResource;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnFonts\CollectionFactory;
use Exception;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Model\AbstractModel;

class PsnFontsRepository implements PsnFontsRepositoryInterface
{
    /**
     * @var PsnFontsResource
     */
    private $fontsResource;

    /**
     * @var PsnFontsFactory
     */
    private $fontsFactory;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var PsnFontsSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    public function __construct(
        PsnFontsResource $fontsResource,
        CollectionFactory $collectionFactory,
        PsnFontsFactory $fontsFactory,
        CollectionProcessorInterface $collectionProcessor,
        PsnFontsSearchResultsInterfaceFactory $searchResultsFactory
    ) {
        $this->fontsResource = $fontsResource;
        $this->fontsFactory = $fontsFactory;
        $this->collectionFactory = $collectionFactory;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    public function getById(int $fontId): PsnFontsInterface
    {
        $model = $this->fontsFactory->create();
        $this->fontsResource->load($model, $fontId, 'font_id');
        if (!$model->getFontId()) {
            throw new NoSuchEntityException(__('font doesn\'t exist'));
        }
        return $model;
    }

    public function save(PsnFontsInterface $model): PsnFontsInterface
    {
        try {
            /** @var PsnFontsInterface|AbstractModel $model */
            $this->fontsResource->save($model);
        } catch (Exception $e) {
            throw new CouldNotSaveException(__('Could not save font: %1', $e->getMessage()));
        }
        return $this->getById($model->getFontId());
    }

    /**
     * {@inheritDoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria): PsnFontsSearchResultsInterface
    {
        $collection = $this->collectionFactory->create()->joinFontsOverride();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    public function delete(PsnFontsInterface $model): bool
    {
        /** @var PsnFontsInterface|AbstractModel $model */
        try {
            $font = $this->getById($model->getFontId());
            $this->fontsResource->delete($font);
        } catch (Exception $e) {
            throw new StateException(__('Unable to remove font: %1', $e->getMessage()));
        }
        return true;
    }

    public function deleteById(int $fontId): bool
    {
        return $this->delete($this->getById($fontId));
    }

    public function saveStoreData(PsnFontsInterface $font, array $storeData): void
    {
        $connection = $this->fontsResource->getConnection();
        $storeId = $storeData['store_id'];
        $existingStoreData = $font->getStoreData($storeId);
        if ($existingStoreData) {
            $this->fontsResource->getConnection()->update(
                $connection->getTableName(PsnFontsResource::TABLE_OVERRIDES),
                [
                    'name' => $storeData['name'],
                    'status' => $storeData['status'],
                    'preview_text' => $storeData['preview_text'],
                    'font_size' => $storeData['font_size'],
                ],
                ['font_id=?' => $font->getFontId(), 'store_id=?' => $storeId]
            );
        } else {
            $this->fontsResource->getConnection()->insert(
                $connection->getTableName(PsnFontsResource::TABLE_OVERRIDES),
                [
                    'font_id' => $font->getFontId(), 'store_id' => $storeId,
                    'name' => $storeData['name'],
                    'status' => $storeData['status'],
                    'preview_text' => $storeData['preview_text'],
                    'font_size' => $storeData['font_size'],
                ]
            );
        }
    }

    public function removeStoreData(int $fontId, int $storeId): void
    {
        $connection = $this->fontsResource->getConnection();
        $this->fontsResource->getConnection()->delete(
            $connection->getTableName(PsnFontsResource::TABLE_OVERRIDES),
            ['font_id=?' => $fontId, 'store_id=?' => $storeId]
        );
    }
}
