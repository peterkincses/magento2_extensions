<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

interface PsnFontsSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get list of fonts
     *
     * @return PsnFontsInterface[]
     */
    public function getItems();

    /**
     * Set list of fonts
     *
     * @param PsnFontsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
