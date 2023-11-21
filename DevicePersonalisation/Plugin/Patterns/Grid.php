<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\Patterns;

use Magento\Framework\DB\Select;

class Grid
{
    /**
     * @var string
     */
    public static $table = 'psn_patterns';

    /**
     * {@inheritDoc}
     */
    public function afterSearch($intercepter, $collection)
    {
        if ($collection->getMainTable() == $collection->getConnection()->getTableName(self::$table)) {
            $where = $collection->getSelect()->getPart(Select::WHERE);
            foreach ($where as $k => $condition) {
                if (stripos($condition, "status") !== false) {
                    $condition = str_replace("`status`", "if(`override_table`.`pattern_id` is null, `main_table`.`status`, `override_table`.`status`)", $condition);
                    $where[$k] = $condition;
                    break;
                }
            }

            $collection->getSelect()->setPart(Select::WHERE, $where);
        }
        return $collection;
    }
}
