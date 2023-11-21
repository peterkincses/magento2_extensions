<?php

declare(strict_types=1);

namespace BAT\Yoti\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class IntegrationTypeList implements OptionSourceInterface
{
    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        $options = ['pnp' => 'Point to Point', 'middleware' => 'Middleware'];
        return $options;
    }
}
