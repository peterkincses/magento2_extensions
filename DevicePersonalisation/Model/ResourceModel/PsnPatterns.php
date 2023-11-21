<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel;

use BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\AbstractModel;

/**
 * Class PsnPatterns
 */
class PsnPatterns extends AbstractDb
{
    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(PsnPatternsInterface::TABLE, PsnPatternsInterface::PATTERN_ID);
    }

    /**
     * @inheritdoc
     */
    protected function _afterLoad(AbstractModel $object)
    {
        if ($object->getId()) {
            $stores = $this->getStoreIds((int) $object->getId());
            $object->setData('store_id', $stores);
            $object->setData('stores', $stores);
        }

        return parent::_afterLoad($object);
    }

    public function getStoreIds(int $id): array
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable(PsnPatternsInterface::TABLE_OVERRIDES),
            'store_id'
        )->where(
            'pattern_id = :pattern_id'
        );
        $binds = [':pattern_id' => (int) $id];

        return $connection->fetchCol($select, $binds);
    }

    /**
     * {@inheritDoc}
     */
    public function getStoreData($patternId, $storeId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable(PsnPatternsInterface::TABLE_OVERRIDES)
        )->where(
            'store_id = :store_id and pattern_id = :pattern_id'
        );
        $binds = [':store_id' => (int) $storeId, ':pattern_id' => (int) $patternId];

        return $connection->fetchRow($select, $binds);
    }
}
