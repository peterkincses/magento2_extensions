<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api;

use BAT\DevicePersonalisation\Api\Data\PsnFontsInterface;
use BAT\DevicePersonalisation\Api\Data\PsnFontsSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;

interface PsnFontsRepositoryInterface
{
    /**
     * Retrieve font.
     *
     * @param int $fontId
     * @return \BAT\DevicePersonalisation\Api\Data\PsnFontsInterface
     */
    public function getById(int $fontId);

    /**
     * Save font
     *
     * @param \BAT\DevicePersonalisation\Api\Data\PsnFontsInterface $model
     * @return \BAT\DevicePersonalisation\Api\Data\PsnFontsInterface
     */
    public function save(PsnFontsInterface $model);

    /**
     * Retrieve fonts matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \BAT\DevicePersonalisation\Api\Data\PsnFontsSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete font
     *
     * @param \BAT\DevicePersonalisation\Api\Data\PsnFontsInterface $model
     */
    public function delete(PsnFontsInterface $model);

    /**
     * Delete font by id
     *
     * @param int $fontId
     * @return bool
     */
    public function deleteById(int $fontId): bool;

    /**
     * Save font store data
     *
     * @param BAT\DevicePersonalisation\Api\Data\PsnFontsInterface $font
     * @param $data
     * @return mixed
     */
    public function saveStoreData(PsnFontsInterface $font, array $data);

    /**
     * Remove store data
     *
     * @param $fontId
     * @param $storeId
     * @return mixed
     */
    public function removeStoreData(int $fontId, int $storeId);
}
