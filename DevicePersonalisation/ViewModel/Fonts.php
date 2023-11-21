<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\ViewModel;

use BAT\DevicePersonalisation\Helper\Data as PsnHelper;
use BAT\DevicePersonalisation\Ui\Component\Fonts\Edit\DataProvider;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use BAT\DevicePersonalisation\Api\PsnFontsRepositoryInterface;

class Fonts implements ArgumentInterface
{
    /**
     * @var PsnFontsRepositoryInterface
     */
    protected $psnFontsRepository;

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
     * @var PsnHelper
     */
    private $psnHelper;

    public function __construct(
        StoreManagerInterface $storeManager,
        PsnFontsRepositoryInterface $psnFontsRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortBuilder,
        PsnHelper $psnHelper
    ) {
        $this->storeManager = $storeManager;
        $this->psnFontsRepository = $psnFontsRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortBuilder = $sortBuilder;
        $this->psnHelper = $psnHelper;
    }

    public function getFonts(): array
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('store_id', $this->getStoreId(), 'eq')
            ->addFilter('override_table.status', 1, 'eq')
            ->addSortOrder($this->sortBuilder->setField('name')
                ->setAscendingDirection()->create())
            ->create();

        $fonts = $this->psnFontsRepository->getList($searchCriteria)->getItems();

        $result = [];
        $intCtr = 0;
        foreach ($fonts as $font) {
            $result[$intCtr] = $font->getStoreData($this->getStoreId());
            $result[$intCtr]['font_file'] = $this->getFontUrl() . $font->getFontFile();
            if (!isset($result[$intCtr]['name'])) {
                $result[$intCtr]['name'] = $font->getName();
            }
            if (!isset($result[$intCtr]['preview_text'])) {
                $result[$intCtr]['preview_text'] = $font->getPreviewText();
            }
            if (!isset($result[$intCtr]['font_size'])) {
                $result[$intCtr]['font_size'] = $font->getFontSize();
            }
            $intCtr++;
        }
        return $result;
    }

    public function getStoreId(): int
    {
        return (int) $this->storeManager->getStore()->getId();
    }

    public function getFontUrl(): string
    {
        return $this->storeManager
                ->getStore()
                ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . DataProvider::UPLOAD_PATH;
    }

    public function getMaxCharHorizontal(): string
    {
        return $this->psnHelper->getMaxCharHorizontal();
    }

    public function getMaxCharVertical(): string
    {
        return $this->psnHelper->getMaxCharVertical();
    }
}
