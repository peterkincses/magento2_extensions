<?php

declare(strict_types=1);

namespace BAT\Yoti\Model\Source;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

class YotiApprovalAttributeOptions extends AbstractSource
{
    public const NOTCHECKED = 'notchecked';
    public const PENDING    = 'pending';
    public const APPROVED   = 'approved';
    public const NOTAPPROVE = 'notapproved';
    public const OLDNOTAPPROVED = 'oldnotapproved';
    public const MANUALLYAPPROVED = 'manuallyapproved';

    public function getAllOptions(): array
    {
        $options = [];
        foreach ($this->toArray() as $key => $label) {
            $options[] = [
                'value' => $key,
                'label' => $label,
            ];
        }
        return $options;
    }

    public function toArray(): array
    {
        return [
            self::NOTCHECKED => __('Not Checked'),
            self::PENDING => __('Pending'),
            self::APPROVED => __('Approved'),
            self::NOTAPPROVE => __('Not Approved'),
            self::OLDNOTAPPROVED => __('Old Not Approved'),
            self::MANUALLYAPPROVED => __('Manually Approved'),
        ];
    }
}
