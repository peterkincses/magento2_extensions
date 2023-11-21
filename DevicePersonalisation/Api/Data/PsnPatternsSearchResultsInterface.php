<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface PsnPatternsSearchResultsInterface
 */
interface PsnPatternsSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get list of patterns
     *
     * @return BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface[]
     */
    public function getItems();

    /**
     * Set list of patterns
     *
     * @param \BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface[] $items
     * @return \BAT\DevicePersonalisation\Api\Data\PsnPatternsSearchResultsInterface
     */
    public function setItems(array $items);
}
