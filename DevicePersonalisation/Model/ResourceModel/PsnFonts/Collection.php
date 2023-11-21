<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel\PsnFonts;

use BAT\DevicePersonalisation\Api\Data\PsnFontsInterface;
use BAT\DevicePersonalisation\Model\PsnFonts;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnFonts as PsnFontsResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = PsnFontsInterface::FONT_ID;

    public function _construct(): void
    {
        $this->_init(PsnFonts::class, PsnFontsResource::class);
        $this->_map['fields']['font_id'] = 'main_table.font_id';
    }

    /**
     * {@inheritDoc}
     */
    public function joinFontsOverride()
    {
        $this->getSelect()->joinLeft(
            ['override_table' => $this->getTable('psn_fonts_overrides')],
            'main_table.font_id = override_table.font_id ',
            [
                'name_localized' => 'IF(override_table.font_id is null or override_table.name is null, main_table.name, override_table.name)',
                'status' => 'IF(override_table.font_id is null or override_table.status is null, main_table.status, override_table.status)',
                'store_id',
            ]
        );

        return $this;
    }
}
