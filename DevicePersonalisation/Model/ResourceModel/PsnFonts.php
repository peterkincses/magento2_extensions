<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel;

use BAT\DevicePersonalisation\Api\Data\PsnFontsInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PsnFonts extends AbstractDb
{
    public const TABLE             = 'psn_fonts'; // Db table
    public const TABLE_OVERRIDES   = 'psn_fonts_overrides'; // Db table
    
    /**
     * @inheritdoc
     */
    protected function _construct(): void
    {
        $this->_init(self::TABLE, PsnFontsInterface::FONT_ID);
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
            $this->getTable(self::TABLE_OVERRIDES),
            'store_id'
        )->where(
            'font_id = :font_id'
        );
        $binds = [':font_id' => (int) $id];

        return $connection->fetchCol($select, $binds);
    }

    /**
     * {@inheritDoc}
     */
    public function getStoreData($fontId, $storeId)
    {
        $connection = $this->getConnection();

        $select = $connection->select()->from(
            $this->getTable(self::TABLE_OVERRIDES)
        )->where(
            'store_id = :store_id and font_id = :font_id'
        );
        $binds = [':store_id' => (int) $storeId, ':font_id' => (int) $fontId];

        return $connection->fetchRow($select, $binds);
    }
}
