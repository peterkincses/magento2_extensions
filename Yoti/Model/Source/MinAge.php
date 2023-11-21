<?php

declare(strict_types=1);

namespace BAT\Yoti\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class MinAge implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        for ($i = 15; $i <= 30; $i++) {
            $options[] = ['value' => $i, 'label' => $i];
        }
        return $options;
    }
}
