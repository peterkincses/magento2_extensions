<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api;

use BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface;
use BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

interface PsnRestrictedWordsRepositoryInterface
{
    /**
     * Save restricted word
     *
     * @param \BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface $psnRestrictedWordsData
     * @return \BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(PsnRestrictedWordsInterface $psnRestrictedWordsData): PsnRestrictedWordsInterface;

    /**
     * Retrieve restricted word
     *
     * @param int $wordId
     * @return \BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById(int $wordId): PsnRestrictedWordsInterface;

    /**
     * Retrieve restricted words matching the specified criteria
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria): PsnRestrictedWordsSearchResultsInterface;

    /**
     * Delete restricted word
     *
     * @param \BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface $psnRestrictedWordsData
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(PsnRestrictedWordsInterface $psnRestrictedWordsData): bool;

    /**
     * Delete restricted words by ID
     *
     * @param int $wordId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById(int $wordId): bool;
}
