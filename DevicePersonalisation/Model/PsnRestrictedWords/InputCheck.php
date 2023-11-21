<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\PsnRestrictedWords;

use BAT\DevicePersonalisation\Api\PsnRestrictedWordsRepositoryInterface as PsnRestrictedWordsRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

class InputCheck
{
    public function __construct(
        PsnRestrictedWordsRepository $psnRestrictedWordsRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->psnRestrictedWordsRepository = $psnRestrictedWordsRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function isValid(string $inputString, int $storeId): bool
    {
        $inputArray = explode(' ', strtolower($inputString));
        $restrictedWords = $this->getRestrictedWords($storeId);

        foreach ($inputArray as $val) {
            if (in_array($val, $restrictedWords)) {
                return false;
            }
        }
        return true;
    }

    public function getRestrictedWords(int $storeId): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', $storeId, 'eq')
            ->create();
        $restrictedWords = $this->psnRestrictedWordsRepository->getList($searchCriteria)->getItems();
        $result = [];
        $intCtr = 0;
        foreach ($restrictedWords as $restrictedWord) {
            $result[$intCtr] = strtolower($restrictedWord->getRestrictedWord());
            $intCtr++;
        }
        return $result;
    }
}
