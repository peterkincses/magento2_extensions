<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Service;

use BAT\DevicePersonalisation\Api\Data\PsnFontsInterface;
use BAT\DevicePersonalisation\Api\PsnFontsRepositoryInterface;
use BAT\DevicePersonalisation\Ui\Component\Fonts\Edit\DataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\UrlInterface;
use Magento\Store\Api\Data\StoreInterface;

class GetPsnFontsByStore
{
    public const FONT_ID = 'font_id';
    public const FONT_FILE = 'font';
    public const FONT_NAME = 'font_name';
    public const FONT_SIZE = 'font_size';
    public const PREVIEW_TEXT = 'preview_text';

    private SearchCriteriaBuilder $searchCriteriaBuilder;

    private PsnFontsRepositoryInterface $psnFontsRepository;

    public function __construct(
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PsnFontsRepositoryInterface $psnFontsRepository
    ) {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->psnFontsRepository = $psnFontsRepository;
    }

    /**
     * Retrieve the personalised fonts for the given store
     */
    public function execute(StoreInterface $store): ?array
    {
        $output = [];
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', $store->getId(), 'eq')
            ->addFilter('override_table.status', 1)
            ->create();

        $list = $this->psnFontsRepository->getList($searchCriteria);

        foreach ($list->getItems() as $item) {
            $storeData = $item->getStoreData($store->getId());
            $output[] = [
                self::FONT_ID => $item->getFontId(),
                self::FONT_FILE => $storeData[PsnFontsInterface::FONT_FILE] ?? $item->getFontFile(),
                self::FONT_NAME => $storeData[PsnFontsInterface::NAME] ?? $item->getName(),
                self::FONT_SIZE => $storeData[PsnFontsInterface::FONT_SIZE] ?? $item->getFontSize(),
                self::PREVIEW_TEXT => $storeData[PsnFontsInterface::PREVIEW_TEXT] ?? $item->getPreviewText(),
            ];
        }

        $this->store = $store;
        $output = array_map([$this, 'addPath'], $output);

        return $output;
    }

    private function addPath(array $item): array
    {
        $baseUrl = $baseUrl ?? $this->store->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

        if (!empty($item[self::FONT_FILE])) {
            $item[self::FONT_FILE] = $baseUrl . DataProvider::UPLOAD_PATH . $item[self::FONT_FILE];
        }

        return $item;
    }
}
