<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Setup\Patch\Data;

use Exception;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchInterface;
use Magento\Store\Model\StoreManager;

class UpdatePsnTextCoordData implements DataPatchInterface
{
    /** @var CollectionFactory */
    private $productCollection;

    /** @var StoreManager */
    private $storeManager;

    /** @var ProductResource */
    private $productResource;

    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var EavSetupFactory */
    private $eavSetupFactory;

    public function __construct(
        ProductCollectionFactory $productCollection,
        StoreManager $storeManager,
        ProductResource $productResource,
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->productCollection = $productCollection;
        $this->storeManager = $storeManager;
        $this->productResource = $productResource;
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply(): void
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);
        $stores = $this->storeManager->getStores();
        $setId = $eavSetup->getAttributeSetId(Product::ENTITY, 'Vuse Device');

        // update psn_text attributes
        foreach ($stores as $store) {
            /** @var ProductCollection $productCollection */
            $productCollection = $this->productCollection->create()
                ->addStoreFilter($store)
                ->addAttributeToSelect('psn_text_coordinates', 'left')
                ->addAttributeToFilter('attribute_set_id', $setId)
                ->addAttributeToFilter('psn_text_coordinates', ['gt' => 0]);
            $items = $productCollection->getItems();

            foreach ($items as $item) {
                $psnTextCoordinates = $item->getPsnTextCoordinates();
                $this->saveProductAttribute($item, 'psn_text_front_top_coord', $psnTextCoordinates);
                $this->saveProductAttribute($item, 'psn_text_back_top_coord', $psnTextCoordinates);
            }
        }

        // remove old attribute
        $eavSetup->removeAttribute(Product::ENTITY, 'psn_text_coordinates');
    }

    /**
     * @param mixed $value
     * @throws Exception
     */
    public function saveProductAttribute(Product $model, string $attributeCode, $value): void
    {
        $model->setData($attributeCode, $value);
        $this->productResource->saveAttribute($model, $attributeCode);
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [PsnTextCoordinateAttr::class];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
