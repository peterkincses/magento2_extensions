<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel\PsnIcon;

use BAT\DevicePersonalisation\Model\PsnIcon;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnIcon as ResourcePsnIcon;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'icon_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'devicepersonalisation_psnicon_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'psnicon_collection';

    public function _construct(): void
    {
        $this->_init(
            PsnIcon::class,
            ResourcePsnIcon::class
        );
    }

    /**
     * {@inheritDoc}
     */
    public function joinIconsOverride()
    {
        $this->getSelect()->joinLeft(
            ['override_table' => $this->getTable('psn_icons_overrides')],
            'main_table.icon_id = override_table.icon_id ',
            [
                'name_localized' => 'IF(override_table.icon_id is null or override_table.name is null, main_table.name, override_table.name)',
                'status' => 'IF(override_table.icon_id is null or override_table.status is null, main_table.status, override_table.status)',
                'store_id',
            ]
        );

        return $this;
    }
}
