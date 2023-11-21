<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Config\Source;

use Magento\CustomerSegment\Model\ResourceModel\Segment\Collection;
use Magento\Framework\Data\OptionSourceInterface;

class CustomerSegments implements OptionSourceInterface
{
    /**
     * @var Collection
     */
    private $customerSegmentCollection;

    /**
     * @var array
     */
    private $options = null;

    public function __construct(
        Collection $customerSegmentCollection
    ) {
        $this->customerSegmentCollection = $customerSegmentCollection;
    }

    /**
     * @inheritDoc
     */
    public function toOptionArray()
    {
        if (is_null($this->options)) {
            $this->options = array_merge(
                [['value' => '', 'label' => __('Please Select')]],
                $this->customerSegmentCollection->toOptionArray()
            );
        }

        return $this->options;
    }
}
