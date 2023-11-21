<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\DataProvider;

use BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface;
use Magento\Framework\Data\Collection;
use Magento\Ui\DataProvider\AddFilterToCollectionInterface;

class AddFulltextFilterToCollection implements AddFilterToCollectionInterface
{
    /**
     * {@inheritDoc}
     */
    public function addFilter(Collection $collection, $field, $condition = null): void
    {
        if (isset($condition['fulltext']) && !empty($condition['fulltext'])) {
            $collection->addFieldToFilter(
                PsnRestrictedWordsInterface::RESTRICTED_WORD,
                ['like' => '%' . $condition['fulltext'] . '%']
            );
        }
    }
}
