<?php

declare(strict_types=1);

namespace BAT\Yoti\Setup\Patch\Data;

use Magento\Cms\Model\BlockFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\Store as MagentoStore;

class AddCmsBlock implements DataPatchInterface
{

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        BlockFactory $blockFactory,
        StoreRepositoryInterface $storeRepository
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->blockFactory = $blockFactory;
        $this->storeRepository = $storeRepository;
    }

    public function apply(): void
    {

        try {
            $store = $this->storeRepository->get('vype_fr_fr_fr');
            $storeId = $store->getId();
        } catch (NoSuchEntityException $e) {
            $storeId = MagentoStore::DEFAULT_STORE_ID;
        }

        $newCmsStaticBlock = [
            'title' => 'Yoti',
            'identifier' => 'yoti-popup',
            'content' => '<div>Yoti popup content</div>',
            'is_active' => 1,
            'stores' => $storeId,
        ];

        $this->moduleDataSetup->startSetup();

        /** @var \Magento\Cms\Model\Block $block */
        $block = $this->blockFactory->create();
        $block->setData($newCmsStaticBlock)->save();

        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
