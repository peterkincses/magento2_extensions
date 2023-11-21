<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PsnItemSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get item list
     *
     * @return \BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface[]
     */
    public function getItems();

    /**
     * Set items list
     *
     * @param \BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface[] $items
     * @return \BAT\DevicePersonalisation\Api\Data\PsnItemSearchResultsInterface
     */
    public function setItems(array $items);
}
