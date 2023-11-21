<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PsnIconSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get icon list
     *
     * @return \BAT\DevicePersonalisation\Api\Data\PsnIconDataInterface[]
     */
    public function getItems();

    /**
     * Set icons list
     *
     * @param \BAT\DevicePersonalisation\Api\Data\PsnIconDataInterface[] $items
     * @return \BAT\DevicePersonalisation\Api\Data\PsnIconSearchResultsInterface
     */
    public function setItems(array $items);
}
