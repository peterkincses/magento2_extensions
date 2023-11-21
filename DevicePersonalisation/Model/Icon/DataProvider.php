<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Icon;

use BAT\DevicePersonalisation\Api\PsnIconRepositoryInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnIcon\Collection;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnIcon\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;

class DataProvider extends ModifierPoolDataProvider
{
    public const IMG_DIR = 'bat_device_personalisation/icons';

    public const IMG_DIR_THUMB = 'bat_device_personalisation/icons/thumbnails';

    /**
     * @var Collection
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
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var PsnIconRepositoryInterface
     */
    protected $psnIconRepository;

    /**
     * @var array
     */
    private $overiddenIconData = [];

    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $blockCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        RequestInterface $request,
        PsnIconRepositoryInterface $psnIconRepository,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $blockCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->psnIconRepository = $psnIconRepository;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    public function getData(): ?array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $overriddenRecord = $this->getOveriddenIconData();
        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $itemData = $item->getData();
            if (!empty($overriddenRecord)) {
                $itemData['name'] = $overriddenRecord['name'] ? $overriddenRecord['name'] : $itemData['name'];
                $itemData['status'] = is_null($overriddenRecord['status']) ? $itemData['status'] : $overriddenRecord['status'];
            }
            
            if (isset($itemData['image'])) {
                $imageUrl = $itemData['image'];
                $imageName = $itemData['image'];
                unset($itemData['image']);
                $itemData['image'][0]['name'] = $imageName;
                $itemData['image'][0]['url'] = $this->storeManager->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . self::IMG_DIR . '/' . $imageUrl;
            } else {
                $itemData['image'] = null;
            }
            if (isset($itemData['thumbnail'])) {
                $imageUrl = $itemData['thumbnail'];
                $imageName = $itemData['thumbnail'];
                unset($itemData['thumbnail']);
                $itemData['thumbnail'][0]['name'] = $imageName;
                $itemData['thumbnail'][0]['url'] = $this->storeManager->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . self::IMG_DIR_THUMB . '/' . $imageUrl;
            } else {
                $itemData['thumbnail'] = null;
            }
            $this->loadedData[$item->getIconId()] = $itemData;
        }
        return $this->loadedData;
    }

    public function getMeta(): array
    {
        $meta = parent::getMeta();
        $storeId = $this->getStoreId();

        if (!empty($storeId) && empty($this->getOveriddenIconData())) {
            $meta['general']['children']['status']['arguments']['data']['config']['service']['template'] =
                'ui/form/element/helper/service';
            $meta['general']['children']['status']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['name']['arguments']['data']['config']['service']['template'] =
                'ui/form/element/helper/service';
            $meta['general']['children']['name']['arguments']['data']['config']['disabled'] = 1;

            $meta['general']['children']['image']['arguments']['data']['config']['visible'] = 0;
            $meta['general']['children']['thumbnail']['arguments']['data']['config']['visible'] = 0;
        } elseif (!empty($storeId)) {
            $overriddenRecord = $this->getOveriddenIconData();

            $meta['general']['children']['status']['arguments']['data']['config']['service']['template'] =
                'ui/form/element/helper/service';

            $meta['general']['children']['name']['arguments']['data']['config']['service']['template'] =
                'ui/form/element/helper/service';

            $meta['general']['children']['image']['arguments']['data']['config']['visible'] = 0;
            $meta['general']['children']['thumbnail']['arguments']['data']['config']['visible'] = 0;
            
            if (is_null($overriddenRecord['status'])) {
                $meta['general']['children']['status']['arguments']['data']['config']['disabled'] = 1;
            }
            if (empty($overriddenRecord['name'])) {
                $meta['general']['children']['name']['arguments']['data']['config']['disabled'] = 1;
            }
        }
        return $meta;
    }

    public function getOveriddenIconData(): ?array
    {
        $iconId = $this->getIconId();
        $storeId = $this->getStoreId();
        if (!$this->overiddenIconData) {
            $this->overiddenIconData =  $this->psnIconRepository->getIconsByStoreId($storeId, $iconId);
        }
        return $this->overiddenIconData;
    }

    public function getStoreId(): ?int
    {
        return (int) $this->request->getParam('store');
    }

    public function getIconId(): ?int
    {
        return (int) $this->request->getParam('icon_id');
    }
}
