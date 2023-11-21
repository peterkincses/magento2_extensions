<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api;

use BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Exception\LocalizedException;
use BAT\DevicePersonalisation\Api\Data\PsnPatternsSearchResultsInterface;

/**
 * Interface PsnPatternsRepositoryInterface
 */
interface PsnPatternsRepositoryInterface
{
    /**
     * @param int $patternId
     * @return \BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface
     */
    public function getById($patternId);

    /**
     * @param \BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface $model
     * @return \BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface
     */
    public function save(PsnPatternsInterface $model);

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \BAT\DevicePersonalisation\Api\Data\PsnPatternsSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param \BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface $model
     */
    public function delete(PsnPatternsInterface $model);

    /**
     * @param int $patternId
     * @return bool
     */
    public function deleteById($patternId): bool;

    /**
     * @param $pattern
     * @param $data
     * @return mixed
     */
    public function saveStoreData($pattern, $data);

    /**
     * @param $patternId
     * @param $storeId
     * @return mixed
     */
    public function removeStoreData($patternId, $storeId);
}
