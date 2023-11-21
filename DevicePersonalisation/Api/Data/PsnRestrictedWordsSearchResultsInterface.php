<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PsnRestrictedWordsSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return \BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param \BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface[] $items
     * @return \BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsSearchResultsInterface
     */
    public function setItems(array $items);
}
