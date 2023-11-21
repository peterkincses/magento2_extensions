<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api;

use BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface;
use BAT\DevicePersonalisation\Api\Data\PsnItemSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface PsnItemRepositoryInterface
{
    /**
     * Save item.
     *
     * @param \BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface $item
     * @return \BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(PsnItemDataInterface $item): PsnItemDataInterface;

    /**
     * Retrieve item.
     *
     * @param int $itemId
     * @return \BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById(int $itemId): PsnItemDataInterface;

    /**
     * Retrieve items matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \BAT\DevicePersonalisation\Api\Data\PsnItemSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): PsnItemSearchResultsInterface;

    /**
     * Delete item.
     *
     * @param \BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(PsnItemDataInterface $item): bool;

    /**
     * Delete item by ID.
     *
     * @param int $itemId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById(int $itemId): bool;
}
