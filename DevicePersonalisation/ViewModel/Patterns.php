<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\ViewModel;

use BAT\DevicePersonalisation\Api\PsnPatternsRepositoryInterface;
use BAT\DevicePersonalisation\Ui\Component\Patterns\Edit\DataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class Patterns implements ArgumentInterface
{
    /**
     * @var PsnPatternsRepositoryInterface
     */
    protected $psnPatternsRepository;

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

    public function __construct(
        StoreManagerInterface $storeManager,
        PsnPatternsRepositoryInterface $psnPatternsRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->psnPatternsRepository = $psnPatternsRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortBuilder = $sortBuilder;
    }

    public function getPatterns(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', $this->getStoreId(), 'eq')
            ->addFilter('override_table.status', 1, 'eq')
            ->addSortOrder($this->sortBuilder->setField('name')
                ->setAscendingDirection()->create())
            ->create();

        $patterns = $this->psnPatternsRepository->getList($searchCriteria)->getItems();

        $result = [];
        $intCtr = 0;
        foreach ($patterns as $pattern) {
            $result[$intCtr] = $pattern->getStoreData($this->getStoreId());
            if (!isset($result[$intCtr]['name'])) {
                $result[$intCtr]['name'] = $pattern->getName();
            }
            $mainImage = $pattern->getImage();
            $result[$intCtr]['image'] = ($mainImage) ? $this->getPatternUrl() . $mainImage : '';
            $thumbnailImage = $pattern->getThumbnail();
            $result[$intCtr]['thumbnail'] = ($thumbnailImage) ? $this->getPatternThumbnailUrl() . $thumbnailImage : '';
            $intCtr++;
        }

        return $result;
    }

    public function getCategories(array $results): array
    {
        $categories = [];
        foreach ($results as $result) {
            foreach ($result as $key => $value) {
                if ($key == 'category_name' && !empty($value)) {
                    array_push($categories, $value);
                }
            }
        }
        return array_unique($categories);
    }

    public function getStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }

    public function getPatternUrl(): string
    {
        return $this->storeManager
                ->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . DataProvider::UPLOAD_PATH;
    }

    public function getPatternThumbnailUrl(): string
    {
        return $this->storeManager
                ->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . DataProvider::UPLOAD_PATH_THUMBNAIL . '/';
    }
}
