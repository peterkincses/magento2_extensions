<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Service;

use BAT\DevicePersonalisation\Api\PsnRestrictedWordsRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Store\Api\Data\StoreInterface;

class GetRestrictedWordsByStore
{
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    private PsnRestrictedWordsRepositoryInterface $psnRestrictedWordsRepository;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PsnRestrictedWordsRepositoryInterface $psnRestrictedWordsRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->psnRestrictedWordsRepository = $psnRestrictedWordsRepository;
    }

    /**
     * Retrieve the personalised restricted words for the given store
     */
    public function execute(StoreInterface $store): ?array
    {
        $output = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', $store->getId())
            ->create();

        $list = $this->psnRestrictedWordsRepository->getList($searchCriteria);

        foreach ($list->getItems() as $item) {
            $output[] = $item->getRestrictedWord();
        }

        return $output;
    }
}
