<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel\PsnPatterns;

use BAT\DevicePersonalisation\Model\PsnPatterns;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnPatterns as PsnPatternsResource;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface;

/**
 * Class Collection
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     *
     * phpcs:disable PSR2.Classes.PropertyDeclaration
     */
    protected $_idFieldName = PsnPatternsInterface::PATTERN_ID;

    /**
     * {@inheritDoc}
     */
    public function _construct()
    {
        $this->_init(PsnPatterns::class, PsnPatternsResource::class);
        $this->_map['fields']['pattern_id'] = 'main_table.pattern_id';
        $this->_map['fields']['name_localized'] = 'override_table.name';
        $this->_map['fields']['category_name'] = 'override_table.category_name';
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function joinPatternsOverride()
    {
        $this->getSelect()->joinLeft(
            ['override_table' => $this->getTable(PsnPatternsInterface::TABLE_OVERRIDES)],
            'main_table.pattern_id = override_table.pattern_id ',
            [
                'name_localized' => 'IF(override_table.pattern_id is null or override_table.name is null, main_table.name, override_table.name)',
                'category_name',
                'status' => 'IF(override_table.pattern_id is null or override_table.status is null, main_table.status, override_table.status)',
                'store_id',
            ]
        );

        return $this;
    }
}
