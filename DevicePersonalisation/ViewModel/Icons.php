<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\ViewModel;

use BAT\DevicePersonalisation\Api\PsnIconRepositoryInterface;
use BAT\DevicePersonalisation\Model\Icon\DataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class Icons implements ArgumentInterface
{
    /**
     * @var PsnIconRepositoryInterface
     */
    protected $psnIconsRepository;

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
        PsnIconRepositoryInterface $psnIconsRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortBuilder
    ) {
        $this->storeManager = $storeManager;
        $this->psnIconsRepository = $psnIconsRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortBuilder = $sortBuilder;
    }

    public function getIcons(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', $this->getStoreId(), 'eq')
            ->addFilter('override_table.status', 1, 'eq')
            ->addSortOrder($this->sortBuilder->setField('name')
                ->setAscendingDirection()->create())
            ->create();

        $icons = $this->psnIconsRepository->getList($searchCriteria)->getItems();

        $result = [];
        $intCtr = 0;
        foreach ($icons as $icon) {
            $result[$intCtr] = $this->psnIconsRepository->getIconsByStoreId($this->getStoreId(), $icon->getIconId());
            $mainImage = $icon->getImage();
            if (!isset($result[$intCtr]['name'])) {
                $result[$intCtr]['name'] = $icon->getName();
            }
            $result[$intCtr]['image'] = ($mainImage) ? $this->getIconUrl() . $mainImage : '';
            $thumbnailImage = $icon->getThumbnail();
            $result[$intCtr]['thumbnail'] = ($thumbnailImage) ? $this->getIconThumbnailUrl() . $thumbnailImage : '';
            $intCtr++;
        }
        return $result;
    }

    public function getStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }

    public function getIconUrl(): string
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . DataProvider::IMG_DIR . '/';
    }

    public function getIconThumbnailUrl(): string
    {
        return $this->storeManager
            ->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . DataProvider::IMG_DIR_THUMB . '/';
    }
}
