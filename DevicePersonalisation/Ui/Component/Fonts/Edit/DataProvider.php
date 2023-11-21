<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\Fonts\Edit;

use BAT\DevicePersonalisation\Model\PsnFonts;
use BAT\DevicePersonalisation\Model\PsnFontsRepository;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnFonts\CollectionFactory as PsnFontsCollection;
use Exception;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\EavAttributeInterface;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Eav\Model\Entity\Attribute\Source\SpecificSourceInterface;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\File\Mime;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;

class DataProvider extends ModifierPoolDataProvider
{
    public const UPLOAD_PATH = 'bat_device_personalisation/fonts/';

    /**
     * @var PsnFontsCollection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var ReadInterface
     */
    protected $_mediaDirectory;

    /**
     * @var Mime
     */
    protected $_mime;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var ContextInterface
     */
    protected $context;

    /**
     * @var PsnFontsRepository
     */
    protected $fontsRepository;

    public function __construct(
        Filesystem $fs,
        Mime $mime,
        ContextInterface $context,
        StoreManagerInterface $storeManager,
        PsnFontsRepository $fontsRepository,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        PsnFontsCollection $fontsCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $fontsCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->_mediaDirectory = $fs->getDirectoryRead(DirectoryList::MEDIA);
        $this->_mime = $mime;
        $this->storeManager = $storeManager;
        $this->context = $context;
        $this->fontsRepository = $fontsRepository;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    public function getData(): ?array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $fontId = $this->context->getRequestParam($this->getRequestFieldName(), null);
        $items = $this->collection->addFieldToFilter('font_id', $fontId)->getItems();

        /**
         * @var PsnFonts $font
         */
        foreach ($items as $font) {
            $this->loadedData[$font->getId()] = $font->getData();
        }

        $data = $this->dataPersistor->get('bat_personalisation_fonts_edit');
        if (!empty($data)) {
            $font = $this->collection->getNewEmptyItem();
            $font->setData($data);
            $this->loadedData[$font->getId()] = $font->getData();
            $this->dataPersistor->clear('bat_personalisation_fonts_edit');
        }

        $currentStore = $this->storeManager->getStore();
        if (!empty($this->loadedData) && $this->loadedData[$fontId]['font_file']) {
            $media_url = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            $image_name = $this->loadedData[$fontId]['font_file'];
            unset($this->loadedData[$fontId]['font_file']);
            $this->loadedData[$fontId]['font_file'][0]['name'] = $image_name;
            $this->loadedData[$fontId]['font_file'][0]['file'] = $image_name;
            $this->loadedData[$fontId]['font_file'][0]['url'] = $media_url . $this->getRelativeFileName($image_name);
            $this->loadedData[$fontId]['font_file'][0]['size'] = $this->getFileSize($image_name);
            $this->loadedData[$fontId]['font_file'][0]['type'] =
                $this->_mime->getMimeType($this->_mediaDirectory->getAbsolutePath($this->getRelativeFileName($image_name)));
        }

        $storeId = (int) $this->context->getRequestParam('store');
        if ($storeId) {
            $model = $this->fontsRepository->getById((int) $fontId);
            $this->loadedData[$fontId]['store'] = $storeId;
            $storeData = $model->getStoreData($storeId);
            if ($storeData) {
                if (isset($storeData['name'])) {
                    $this->loadedData[$fontId]['name'] = $storeData['name'];
                    $this->loadedData[$fontId]['store_name'] = $storeData['name'];
                }
                if (isset($storeData['status'])) {
                    $this->loadedData[$fontId]['status'] = $storeData['status'];
                    $this->loadedData[$fontId]['store_status'] = $storeData['status'];
                }
                if (isset($storeData['preview_text'])) {
                    $this->loadedData[$fontId]['preview_text'] = $storeData['preview_text'];
                    $this->loadedData[$fontId]['store_preview_text'] = $storeData['preview_text'];
                }
                if (isset($storeData['font_size'])) {
                    $this->loadedData[$fontId]['font_size'] = $storeData['font_size'];
                    $this->loadedData[$fontId]['store_font_size'] = $storeData['font_size'];
                }
            }
        }

        return $this->loadedData;
    }

    protected function getRelativeFileName(string $imageName): string
    {
        return self::UPLOAD_PATH . $imageName;
    }

    protected function getFileSize(string $fileName): ?int
    {
        try {
            $fullFileName = sprintf('%s%s', self::UPLOAD_PATH, $fileName);

            $statResults = $this->_mediaDirectory->stat($fullFileName);
            return is_array($statResults) ? $statResults['size'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    public function getMeta(): array
    {
        $meta = parent::getMeta();

        $store = $this->context->getRequestParam('store');
        $fontId = $this->context->getRequestParam('font_id');

        $meta['general']['children']['name']['arguments']['data']['config']['scopeLabel'] = __('Store View');

        if ($store) {
            $meta['general']['children']['name']['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
            if (!isset($this->getData()[$fontId]['store_name'])) {
                $meta['general']['children']['name']['arguments']['data']['config']['disabled'] = 1;
            }

            $meta['general']['children']['status']['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
            if (!isset($this->getData()[$fontId]['store_status'])) {
                $meta['general']['children']['status']['arguments']['data']['config']['disabled'] = 1;
            }
            $meta['general']['children']['preview_text']['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
            if (!isset($this->getData()[$fontId]['store_preview_text'])) {
                $meta['general']['children']['preview_text']['arguments']['data']['config']['disabled'] = 1;
            }
            $meta['general']['children']['font_size']['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
            if (!isset($this->getData()[$fontId]['store_font_size'])) {
                $meta['general']['children']['font_size']['arguments']['data']['config']['disabled'] = 1;
            }
            $meta['general']['children']['font_file']['arguments']['data']['config']['disabled'] = 1;
            $meta['general']['children']['font_file']['arguments']['data']['config']['visible'] = 0;
        }
        return $meta;
    }
}
