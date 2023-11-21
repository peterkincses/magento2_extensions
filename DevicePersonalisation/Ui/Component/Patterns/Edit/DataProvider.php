<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\Patterns\Edit;

use BAT\DevicePersonalisation\Model\PsnPatterns;
use BAT\DevicePersonalisation\Model\PsnPatternsRepository;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnPatterns\CollectionFactory as PsnPatternsCollection;
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

/**
 * Class DataProvider
 */
class DataProvider extends ModifierPoolDataProvider
{
    public const UPLOAD_PATH = 'bat_device_personalisation/patterns/';

    public const UPLOAD_PATH_THUMBNAIL = 'bat_device_personalisation/patterns/thumbnails';

    /**
     * @var PsnPatternsCollection
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
     * @var PsnPatternsRepository
     */
    protected $patternsRepository;

    public function __construct(
        Filesystem $fs,
        Mime $mime,
        ContextInterface $context,
        StoreManagerInterface $storeManager,
        PsnPatternsRepository $patternsRepository,
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        PsnPatternsCollection $patternsCollectionFactory,
        DataPersistorInterface $dataPersistor,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $patternsCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->_mediaDirectory = $fs->getDirectoryRead(DirectoryList::MEDIA);
        $this->_mime = $mime;
        $this->storeManager = $storeManager;
        $this->context = $context;
        $this->patternsRepository = $patternsRepository;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $patternId = $this->context->getRequestParam($this->getRequestFieldName(), null);
        $items = $this->collection->addFieldToFilter('pattern_id', $patternId)->getItems();

        /**
         * @var PsnPatterns $pattern
         */
        foreach ($items as $pattern) {
            $this->loadedData[$pattern->getId()] = $pattern->getData();
        }

        $data = $this->dataPersistor->get('bat_personalisation_patterns_edit');
        if (!empty($data)) {
            $pattern = $this->collection->getNewEmptyItem();
            $pattern->setData($data);
            $this->loadedData[$pattern->getId()] = $pattern->getData();
            $this->dataPersistor->clear('bat_personalisation_patterns_edit');
        }

        $currentStore = $this->storeManager->getStore();
        if (!empty($this->loadedData) && $this->loadedData[$patternId]['image']) {
            $media_url = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            $image_name = $this->loadedData[$patternId]['image'];
            unset($this->loadedData[$patternId]['image']);
            $this->loadedData[$patternId]['image'][0]['name'] = $image_name;
            $this->loadedData[$patternId]['image'][0]['file'] = $image_name;
            $this->loadedData[$patternId]['image'][0]['url'] = $media_url . $this->getRelativeFileName($image_name);
            $this->loadedData[$patternId]['image'][0]['size'] = $this->getFileSize($image_name);
            //$this->loadedData[$page->getId()]['sc_banner_image'][0]['previewType'] = "image";
            $this->loadedData[$patternId]['image'][0]['type'] =
                $this->_mime->getMimeType($this->_mediaDirectory->getAbsolutePath($this->getRelativeFileName($image_name)));
        }
        if (!empty($this->loadedData) && $this->loadedData[$patternId]['thumbnail']) {
            $media_url = $currentStore->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);

            $image_name = $this->loadedData[$patternId]['thumbnail'];
            unset($this->loadedData[$patternId]['thumbnail']);
            $this->loadedData[$patternId]['thumbnail'][0]['name'] = $image_name;
            $this->loadedData[$patternId]['thumbnail'][0]['file'] = $image_name;
            $this->loadedData[$patternId]['thumbnail'][0]['url'] = $media_url . self::UPLOAD_PATH_THUMBNAIL . '/' . $image_name;
            $this->loadedData[$patternId]['thumbnail'][0]['size'] = $this->getThumbnailSize($image_name);
            //$this->loadedData[$page->getId()]['sc_banner_image'][0]['previewType'] = "image";
            $this->loadedData[$patternId]['thumbnail'][0]['type'] =
                $this->_mime->getMimeType($this->_mediaDirectory->getAbsolutePath(self::UPLOAD_PATH_THUMBNAIL . '/' . $image_name));
        }

        $storeId = $this->context->getRequestParam('store');
        if ($storeId) {
            $model = $this->patternsRepository->getById($patternId);
            $this->loadedData[$patternId]['store'] = $storeId;
            $storeData = $model->getStoreData($storeId);
            if ($storeData) {
                if (isset($storeData['name'])) {
                    $this->loadedData[$patternId]['name'] = $storeData['name'];
                    $this->loadedData[$patternId]['store_name'] = $storeData['name'];
                }
                if (isset($storeData['category_name'])) {
                    $this->loadedData[$patternId]['category_name'] = $storeData['category_name'];
                }
                if (isset($storeData['status'])) {
                    $this->loadedData[$patternId]['status'] = $storeData['status'];
                    $this->loadedData[$patternId]['store_status'] = $storeData['status'];
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

    protected function getThumbnailSize(string $fileName): ?int
    {
        try {
            $fullFileName = sprintf('%s%s', self::UPLOAD_PATH, $fileName);

            $statResults = $this->_mediaDirectory->stat($fullFileName);
            return is_array($statResults) ? $statResults['size'] : null;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getMeta()
    {
        $meta = parent::getMeta();

        $store = $this->context->getRequestParam('store');
        $patternId = $this->context->getRequestParam('pattern_id');

        $meta['general']['children']['name']['arguments']['data']['config']['scopeLabel'] = __('Store View');
        $meta['general']['children']['category_name']['arguments']['data']['config']['scopeLabel'] = __('Store View');

        if ($store) {
            $meta['general']['children']['name']['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
            if (!isset($this->getData()[$patternId]['store_name'])) {
                $meta['general']['children']['name']['arguments']['data']['config']['disabled'] = 1;
            }

            $meta['general']['children']['status']['arguments']['data']['config']['service']['template'] = 'ui/form/element/helper/service';
            if (!isset($this->getData()[$patternId]['store_status'])) {
                $meta['general']['children']['status']['arguments']['data']['config']['disabled'] = 1;
            }

            $meta['general']['children']['image']['arguments']['data']['config']['disabled'] = 1;
            $meta['general']['children']['thumbnail']['arguments']['data']['config']['disabled'] = 1;
        } else {
            $meta['general']['children']['category_name']['arguments']['data']['config']['disabled'] = 1;
            $meta['general']['children']['category_name']['arguments']['data']['config']['visible'] = 0;
        }
        return $meta;
    }
}
