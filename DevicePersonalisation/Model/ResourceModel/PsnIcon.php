<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel;

use Magento\Framework\DB\Select;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class PsnIcon extends AbstractDb
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var MetadataPool
     */
    protected $metadataPool;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        EntityManager $entityManager,
        MetadataPool $metadataPool,
        string $connectionName = null
    ) {
        $this->storeManager = $storeManager;
        $this->entityManager = $entityManager;
        $this->metadataPool = $metadataPool;
        parent::__construct($context, $connectionName);
    }

    protected function _construct(): void
    {
        $this->_init('psn_icons', 'icon_id');
    }

    public function save(AbstractModel $object): self
    {
        $this->entityManager->save($object);
        return $this;
    }

    public function delete(AbstractModel $object): self
    {
        $this->entityManager->delete($object);
        return $this;
    }
}
