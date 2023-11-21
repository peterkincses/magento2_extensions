<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Config\Source\Fonts;

use Magento\Framework\Data\OptionSourceInterface;

class Enabled implements OptionSourceInterface
{
    public const NO  = 0;
    public const YES = 1;

    public function toOptionArray(): array
    {
        return [
            ['value' => self::NO, 'label' => __('No')],
            ['value' => self::YES, 'label' => __('Yes')],
        ];
    }
}
