<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\PsnRestrictedWords;

use BAT\DevicePersonalisation\Model\ResourceModel\PsnRestrictedWords\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\Modifier\PoolInterface;
use Magento\Ui\DataProvider\ModifierPoolDataProvider;

class DataProvider extends ModifierPoolDataProvider
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var mixed[]
     */
    private $loadedData;

    /**
     * @param mixed[] $meta
     * @param mixed[] $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = [],
        PoolInterface $pool = null
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManager;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data, $pool);
    }

    /**
     * @return mixed[]
     */
    public function getData(): ?array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $itemData = $item->getData();
            $this->loadedData[$item->getWordId()] = $itemData;
        }

        return $this->loadedData;
    }
}
