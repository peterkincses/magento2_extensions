<?php

declare(strict_types=1);

namespace BAT\Yoti\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class AllowedUncertainty implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = [];
        for ($i = 0; $i <= 6; $i++) {
            $options[] = ['value' => $i, 'label' => $i];
        }
        return $options;
    }
}
