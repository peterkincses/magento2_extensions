<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel\PsnIcon\Relation\Store;

use BAT\DevicePersonalisation\Model\ResourceModel\PsnIcon as PsnIconResource;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

class SaveHandler implements ExtensionInterface
{
    /**
     * @var PsnIconResource
     */
    protected $psnIconResource;

    public function __construct(
        PsnIconResource $psnIconResource
    ) {
        $this->psnIconResource = $psnIconResource;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($entity, $arguments = [])
    {
        if ($entity->getStoreId()) {
            $result = [];
            $connection = $this->psnIconResource->getConnection();
            $table = $this->psnIconResource->getTable('psn_icons_overrides');
            if ($entity->getChangedStatus()) {
                $result = $this->getIconsDataByStoreIconId($entity);
                if (empty($result)) {
                    $connection->insert($table, [
                        'icon_id' => (int) $entity->getIconId(),
                        'store_id' => (int) $entity->getStoreId(),
                        'name' => $entity->getName(),
                        'status' => $entity->getStatusVal(),
                    ]);
                    return $entity;
                }
            }
            // First delete all existing relations:
            $where = [
                'icon_id = ?' => (int) $entity->getIconId(),
                'store_id = ?' => (int) $entity->getStoreId(),
            ];
            $connection->delete($table, $where);

            // Re-create the relations:
            if (!$entity->getIsDelete()) {
                if ($entity->getChangedStatus()) {
                    $connection->insert($table, [
                        'icon_id' => (int) $entity->getIconId(),
                        'store_id' => (int) $entity->getStoreId(),
                        'name' => $result['name'],
                        'status' => $entity->getStatusVal(),
                    ]);
                } else {
                    $connection->insert($table, [
                        'icon_id' => (int) $entity->getIconId(),
                        'store_id' => (int) $entity->getStoreId(),
                        'name' => $entity->getOverridenName(),
                        'status' => $entity->getOverridenStatus(),
                    ]);
                }
            }
        }
        return $entity;
    }

    public function getIconsDataByStoreIconId(object $entity): ?array
    {
        $connection = $this->psnIconResource->getConnection();
        $table = $this->psnIconResource->getTable('psn_icons_overrides');
        $query = $connection->select()->from($table, ['*'])
                ->where('icon_id = ? AND store_id', (int) $entity->getIconId(), (int) $entity->getStoreId());

        $result =  $connection->fetchRow($query);
        if (!$result) {
            return [];
        }
        return $result;
    }
}
