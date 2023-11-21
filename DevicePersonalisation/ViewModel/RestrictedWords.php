<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\ViewModel;

use BAT\DevicePersonalisation\Api\PsnRestrictedWordsRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class RestrictedWords implements ArgumentInterface
{
    /**
     * @var PsnRestrictedWordsRepositoryInterface
     */
    protected $psnRWRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var SortOrderBuilder
     */
    protected $sortBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        StoreManagerInterface $storeManager,
        PsnRestrictedWordsRepositoryInterface $psnRWRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortBuilder,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->storeManager = $storeManager;
        $this->psnRWRepository = $psnRWRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortBuilder = $sortBuilder;
        $this->scopeConfig = $scopeConfig;
    }

    public function getRestrictedWords(): string
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', $this->getStoreId(), 'eq')
            ->addSortOrder($this->sortBuilder->setField('restricted_word')
                ->setAscendingDirection()->create())
            ->create();

        $restrictedWords = $this->psnRWRepository->getList($searchCriteria)->getItems();

        $result = [];
        $intCtr = 0;
        foreach ($restrictedWords as $restrictedWord) {
            $result[$intCtr] = strtolower($restrictedWord->getRestrictedWord());
            $intCtr++;
        }
        return json_encode($result);
    }

    public function getStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }
}
