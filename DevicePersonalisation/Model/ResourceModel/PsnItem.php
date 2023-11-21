<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel;

use BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class PsnItem extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('psn_item', PsnItemDataInterface::ENTITY_ID);
    }
}
