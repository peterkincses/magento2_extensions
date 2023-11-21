<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api;

use BAT\DevicePersonalisation\Api\Data\PsnIconDataInterface;
use BAT\DevicePersonalisation\Api\Data\PsnIconSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface PsnIconRepositoryInterface
{
    /**
     * Save icon.
     *
     * @param \BAT\DevicePersonalisation\Api\Data\PsnIconDataInterface $icon
     * @return \BAT\DevicePersonalisation\Api\Data\PsnIconDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(PsnIconDataInterface $icon): PsnIconDataInterface;

    /**
     * Retrieve icon.
     *
     * @param int $iconId
     * @return \BAT\DevicePersonalisation\Api\Data\PsnIconDataInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById(int $iconId): PsnIconDataInterface;

    /**
     * Retrieve icons matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \BAT\DevicePersonalisation\Api\Data\PsnIconSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): PsnIconSearchResultsInterface;

    /**
     * Delete icon.
     *
     * @param \BAT\DevicePersonalisation\Api\Data\PsnIconDataInterface $icon
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(PsnIconDataInterface $icon): bool;

    /**
     * Delete icon by ID.
     *
     * @param int $iconId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById(int $iconId): bool;

    /**
     * Load icon data collection by given store id and icon id
     *
     * @param int $storeid
     * @param int $iconId
     * @return mixed[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     *
     */
    public function getIconsByStoreId(int $storeId, int $iconId): array;
}
