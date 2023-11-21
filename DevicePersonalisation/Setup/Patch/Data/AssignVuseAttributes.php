<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use BAT\Setup\Setup\Patch\Data\AddVuseDKAttributeSetsPatch;

class AssignVuseAttributes implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        EavSetupFactory $eavSetupFactory
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    public function apply(): void
    {
        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $attributeSets = ['Vuse Device'];
        $attributes = ['psn_is_personalisable', 'psn_front_image', 'psn_text_coordinates', 'psn_image_coordinates', 'psn_background_image'];

        foreach ($attributes as $proAttribute) {
            foreach ($attributeSets as $set) {
                $eavSetup->addAttributeGroup(
                    Product::ENTITY,
                    $set,
                    'Personalisation'
                );
                $eavSetup->addAttributeToSet(
                    Product::ENTITY,
                    $set,
                    'Personalisation',
                    $proAttribute
                );
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public static function getDependencies(): array
    {
        return [AddVuseDKAttributeSetsPatch::class];
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
