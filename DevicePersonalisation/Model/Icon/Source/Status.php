<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Icon\Source;

use BAT\DevicePersonalisation\Model\PsnIcon;
use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * @var PsnIcon
     */
    protected $psnIcon;

    public function __construct(
        PsnIcon $psnIcon
    ) {
        $this->psnIcon = $psnIcon;
    }

    public function toOptionArray(): array
    {
        $availableOptions = $this->psnIcon->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
