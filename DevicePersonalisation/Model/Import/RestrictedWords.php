<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Import;

use Exception;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\ImportExport\Helper\Data as ImportHelper;
use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\Entity\AbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\ResourceModel\Helper;
use Magento\ImportExport\Model\ResourceModel\Import\Data;
use BAT\DevicePersonalisation\Model\PsnRestrictedWordsFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class RestrictedWords manage import
 */
class RestrictedWords extends AbstractEntity
{
    public const ENTITY_CODE = 'restricted_word';
    public const TABLE = 'psn_restricted_words';
    public const ENTITY_ID_COLUMN = 'word_id';

    /**
     * @var boolean
     */
    protected $needColumnCheck = true;

    /**
     * @var boolean
     */
    protected $logInHistory = true;

    /**
     * @var array
     *
     * phpcs:disable PSR2.Classes.PropertyDeclaration
     */
    protected $_permanentAttributes = [
        'word_id',
    ];
    // phpcs:enable

    /**
     * @var array
     */
    protected $validColumnNames = [
        'word_id',
        'store_id',
        'restricted_word',
    ];

    /**
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var PsnRestrictedWordsFactory
     */
    private $psnRestrictedWordsFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Courses constructor.
     *
     */
    public function __construct(
        JsonHelper $jsonHelper,
        ImportHelper $importExportData,
        Data $importData,
        ResourceConnection $resource,
        Helper $resourceHelper,
        ProcessingErrorAggregatorInterface $errorAggregator,
        PsnRestrictedWordsFactory $psnRestrictedWordsFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_importExportData = $importExportData;
        $this->_resourceHelper = $resourceHelper;
        $this->_dataSourceModel = $importData;
        $this->resource = $resource;
        $this->connection = $resource->getConnection(ResourceConnection::DEFAULT_CONNECTION);
        $this->errorAggregator = $errorAggregator;
        $this->psnRestrictedWordsFactory = $psnRestrictedWordsFactory;
        $this->storeManager = $storeManager;
        $this->initMessageTemplates();
    }

    /**
     * Entity type code getter.
     *
     */
    public function getEntityTypeCode(): string
    {
        return static::ENTITY_CODE;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    public function getValidColumnNames(): array
    {
        return $this->validColumnNames;
    }

    /**
     * {@inheritDoc}
     */
    public function validateRow(array $rowData, $rowNum)
    {
        try {
            if ($this->getBehavior() == Import::BEHAVIOR_DELETE) {
                return !$this->getErrorAggregator()->isRowInvalid($rowNum);
            }
            $restrictedWord = $rowData['restricted_word'] ?? '';
            $storeId = (int) $rowData['store_id'] ?? 0;

            if (!$restrictedWord) {
                $this->addRowError('WordIsRequired', $rowNum);
            }

            if (!$storeId) {
                $this->addRowError('StoreIsRequired', $rowNum);
            } else {
                $store = $this->storeManager->getStore($storeId);
            }

            if ($restrictedWord && $storeId) {
                $result = $this->psnRestrictedWordsFactory->create()->getCollection()
                    ->addFieldToFilter("store_id", ["eq" => $storeId]);
                $result->getSelect()
                    ->where('restricted_word = ? COLLATE utf8_bin', trim($restrictedWord));

                if ($result->getSize()) {
                    $this->addRowError('DuplicateIsNotAllow', $rowNum);
                }
            }

            if (isset($this->_validatedRows[$rowNum])) {
                return !$this->getErrorAggregator()->isRowInvalid($rowNum);
            }

            $this->_validatedRows[$rowNum] = true;
        } catch (NoSuchEntityException $e) {
            $this->addRowError('StoreIsNotExist', $rowNum);
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNum);
    }

    /**
     * Init Error Messages
     */
    private function initMessageTemplates(): void
    {
        $this->addMessageTemplate(
            'WordIsRequired',
            __('The restricted word cannot be empty.')
        );
        $this->addMessageTemplate(
            'StoreIsRequired',
            __('Store should be greater than 0.')
        );
        $this->addMessageTemplate(
            'DuplicateIsNotAllow',
            __('Restricted word should be unique.')
        );
        $this->addMessageTemplate(
            'StoreIsNotExist',
            __('Store is not exist.')
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function _importData(): bool
    {
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->deleteEntity();
                break;
            case Import::BEHAVIOR_REPLACE:
                $this->saveAndReplaceEntity();
                break;
            case Import::BEHAVIOR_APPEND:
                $this->saveAndReplaceEntity();
                break;
        }

        return true;
    }

    /**
     * Delete entities
     *
     */
    private function deleteEntity(): bool
    {
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            foreach ($bunch as $rowNum => $rowData) {
                $this->validateRow($rowData, $rowNum);

                if (!$this->getErrorAggregator()->isRowInvalid($rowNum)) {
                    $rowId = $rowData[static::ENTITY_ID_COLUMN];
                    $rows[] = $rowId;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);
                }
            }
        }

        if ($rows) {
            return $this->deleteEntityFinish(array_unique($rows));
        }

        return false;
    }

    /**
     * Save and replace entities
     *
     */
    private function saveAndReplaceEntity(): void
    {
        $behavior = $this->getBehavior();
        $rows = [];
        while ($bunch = $this->_dataSourceModel->getNextBunch()) {
            $entityList = [];

            foreach ($bunch as $rowNum => $row) {
                if (!$this->validateRow($row, $rowNum)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNum);

                    continue;
                }

                $rowId = $row[static::ENTITY_ID_COLUMN];
                $rows[] = $rowId;
                $columnValues = [];

                foreach ($this->getAvailableColumns() as $columnKey) {
                    $columnValues[$columnKey] = $row[$columnKey] != "" ? trim($row[$columnKey]) : "";
                }

                $entityList[$rowId][] = $columnValues;
                $this->countItemsCreated += (int) !isset($row[static::ENTITY_ID_COLUMN]);
                $this->countItemsUpdated += (int) isset($row[static::ENTITY_ID_COLUMN]);
            }

            if (Import::BEHAVIOR_REPLACE === $behavior) {
                if ($rows && $this->deleteEntityFinish(array_unique($rows))) {
                    $this->saveEntityFinish($entityList);
                }
            } elseif (Import::BEHAVIOR_APPEND === $behavior) {
                $this->saveEntityFinish($entityList);
            }
        }
    }

    /**
     * Save entities
     *
     * @param array $entityData
     *
     */
    private function saveEntityFinish(array $entityData): bool
    {
        if ($entityData) {
            $tableName = $this->connection->getTableName(static::TABLE);
            $rows = [];

            foreach ($entityData as $entityRows) {
                foreach ($entityRows as $row) {
                    $rows[] = $row;
                }
            }
            if ($rows) {
                $this->connection->insertOnDuplicate($tableName, $rows, $this->getAvailableColumns());

                return true;
            }

            return false;
        }
    }

    /**
     * Delete entities
     *
     * @param array $entityIds
     *
     */
    private function deleteEntityFinish(array $entityIds): bool
    {
        if ($entityIds) {
            try {
                $this->countItemsDeleted += $this->connection->delete(
                    $this->connection->getTableName(static::TABLE),
                    $this->connection->quoteInto(static::ENTITY_ID_COLUMN . ' IN (?)', $entityIds)
                );

                return true;
            } catch (Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * Get available columns
     *
     * @return array
     */
    private function getAvailableColumns(): array
    {
        return $this->validColumnNames;
    }
}
