<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel\PsnItem;

use BAT\DevicePersonalisation\Model\PsnItem;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnItem as PsnItemResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * @var string
     */
    protected $_eventPrefix = 'devicepersonalisation_psnitem_collection';

    /**
     * @var string
     */
    protected $_eventObject = 'psnitem_collection';

    public function _construct(): void
    {
        $this->_init(
            PsnItem::class,
            PsnItemResource::class
        );
    }
}
