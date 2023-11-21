<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Service;

use BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface;
use BAT\DevicePersonalisation\Api\PsnPatternsRepositoryInterface;
use BAT\DevicePersonalisation\Ui\Component\Patterns\Edit\DataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;

class GetPsnPatternsByStore
{
    public const PATTERN_ID = 'pattern_id';
    public const PATTERN_NAME = 'pattern_name';
    public const PATTERN_IMAGE = 'pattern_image';
    public const THUMBNAIL = 'thumbnail_image';

    private SearchCriteriaBuilder $searchCriteriaBuilder;

    private PsnPatternsRepositoryInterface $psnPatternsRepository;

    private StoreInterface $store;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PsnPatternsRepositoryInterface $psnPatternsRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->psnPatternsRepository = $psnPatternsRepository;
    }

    /**
     * Retrieve the personalised patterns for the given store
     */
    public function execute(StoreInterface $store): ?array
    {
        $output = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', $store->getId(), 'eq')
            ->addFilter('override_table.status', 1)
            ->create();

        $list = $this->psnPatternsRepository->getList($searchCriteria);

        foreach ($list->getItems() as $item) {
            $storeData = $item->getStoreData($store->getId());
            $output[] = [
                self::PATTERN_ID => $item->getPatternId(),
                self::PATTERN_NAME => $storeData[PsnPatternsInterface::NAME] ?? $item->getName(),
                self::PATTERN_IMAGE => $storeData[PsnPatternsInterface::IMAGE] ?? $item->getImage(),
                self::THUMBNAIL => $storeData[PsnPatternsInterface::THUMBNAIL] ?? $item->getThumbnail(),
            ];
        }

        $this->store = $store;
        $output = array_map([$this, 'addPath'], $output);

        return $output;
    }

    /**
     * Add the path to the pattern images
     */
    private function addPath(array $item): array
    {
        $baseUrl = $this->store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        if (!empty($item[self::PATTERN_IMAGE])) {
            $item[self::PATTERN_IMAGE] =  $baseUrl . DataProvider::UPLOAD_PATH . $item[self::PATTERN_IMAGE];
        }

        if (!empty($item[self::THUMBNAIL])) {
            $item[self::THUMBNAIL] =  $baseUrl . DataProvider::UPLOAD_PATH_THUMBNAIL . '/' . $item[self::THUMBNAIL];
        }

        return $item;
    }
}
