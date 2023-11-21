<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Config\Source\Patterns;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Enabled
 */
class Enabled implements OptionSourceInterface
{
    public const NO  = 0;
    public const YES = 1;

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::NO, 'label' => __('No')],
            ['value' => self::YES, 'label' => __('Yes')],
        ];
    }
}
