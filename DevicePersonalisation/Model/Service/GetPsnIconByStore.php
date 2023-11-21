<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Service;

use BAT\DevicePersonalisation\Api\Data\PsnIconDataInterface;
use BAT\DevicePersonalisation\Api\PsnIconRepositoryInterface;
use BAT\DevicePersonalisation\Model\ImageUploader;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;

class GetPsnIconByStore
{
    public const ICON_ID = 'icon_id';
    public const ICON_NAME = 'icon_name';
    public const ICON_IMAGE = 'icon_image';
    public const THUMBNAIL = 'thumbnail_image';

    private SearchCriteriaBuilder $searchCriteriaBuilder;

    private PsnIconRepositoryInterface $psnIconRepository;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PsnIconRepositoryInterface $psnIconRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->psnIconRepository = $psnIconRepository;
    }

    /**
     * Retrieve the personalised icons for the given store
     */
    public function execute(StoreInterface $store): ?array
    {
        $output = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', $store->getId(), 'eq')
            ->addFilter('override_table.status', 1)
            ->create();

        $list = $this->psnIconRepository->getList($searchCriteria);

        foreach ($list->getItems() as $item) {
            $storeData = $this->psnIconRepository->getIconsByStoreId((int) $store->getId(), (int) $item->getIconId());
            $output[] = [
                self::ICON_ID => $item->getIconId(),
                self::ICON_NAME => $storeData[PsnIconDataInterface::ICON_NAME] ?? $item->getIconName(),
                self::ICON_IMAGE => $storeData[PsnIconDataInterface::IMAGE] ?? $item->getImage(),
                self::THUMBNAIL => $storeData[PsnIconDataInterface::THUMBNAIL] ?? $item->getThumbnail(),
            ];
        }

        $this->store = $store;
        $output = array_map([$this, 'addPath'], $output);

        return $output;
    }

    /**
     * Add the path to the icon images
     */
    private function addPath(array $item): array
    {
        $baseUrl = $this->store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        if (!empty($item[self::ICON_IMAGE])) {
            $item[self::ICON_IMAGE] =  $baseUrl . ImageUploader::UPLOAD_IMAGE_PATH . '/' . $item[self::ICON_IMAGE];
        }

        if (!empty($item[self::THUMBNAIL])) {
            $item[self::THUMBNAIL] = $baseUrl . ImageUploader::UPLOAD_THUMBNAIL_PATH . '/' . $item[self::THUMBNAIL];
        }

        return $item;
    }
}
