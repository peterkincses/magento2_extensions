<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\Listing\Columns\Fonts;

use BAT\DevicePersonalisation\Ui\Component\Fonts\Edit\DataProvider;
use Magento\Catalog\Helper\Image;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Listing\Columns\Column;

class Thumbnail extends Column
{
    public const ALT_FIELD = 'title';

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Image
     */
    protected $imageHelper;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Image $imageHelper,
        UrlInterface $urlBuilder,
        StoreManagerInterface $storeManager,
        array $components = [],
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $url = '';
                if ($item[$fieldName] != '') {
                    $url = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . DataProvider::UPLOAD_PATH . $item[$fieldName];
                }
                $item[$fieldName . '_src'] = $url;
                $item[$fieldName . '_alt'] = $this->getAlt($item) ?: '';
                $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                    'bat_personalisation/fonts/edit',
                    ['font_id' => $item['font_id']]
                );
                $item[$fieldName . '_orig_src'] = $url;
            }
        }

        return $dataSource;
    }

    protected function getAlt(array $row): ?string
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return isset($row[$altField]) ? $row[$altField] : null;
    }
}
