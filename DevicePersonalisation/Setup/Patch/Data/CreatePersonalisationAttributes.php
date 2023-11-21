<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Setup\Patch\Data;

use BAT\DevicePersonalisation\Model\Product\Attribute\BackgroundImage;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Frontend\Image;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class CreatePersonalisationAttributes implements DataPatchInterface
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

        $eavSetup->addAttribute(
            'catalog_product',
            'psn_text_coordinates',
            [
                'type' => 'varchar',
                'label' => 'Text Coordinates',
                'input' => 'text',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'required' => false,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            ]
        );

        $eavSetup->addAttribute(
            'catalog_product',
            'psn_image_coordinates',
            [
                'type' => 'varchar',
                'label' => 'Image Coordinates',
                'input' => 'text',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'required' => false,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            ]
        );

        $eavSetup->addAttribute('catalog_product', 'psn_background_image', [
            'type' => 'varchar',
            'label' => 'Personalisation Image - Back',
            'input' => 'media_image',
            'backend' => '',
            'frontend' => Image::class,
            'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
        ]);

        $eavSetup->addAttribute('catalog_product', 'psn_front_image', [
            'type' => 'varchar',
            'label' => 'Personalisation Image - Front',
            'input' => 'media_image',
            'backend' => '',
            'frontend' => Image::class,
            'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            'visible' => true,
            'required' => false,
            'user_defined' => true,
            'visible_on_front' => true,
            'used_in_product_listing' => true,
        ]);

        $attributeSets = ['Glo Device', 'Vype Device', 'Glo Tabak Heater'];

        $attributes = ['psn_text_coordinates', 'psn_image_coordinates', 'psn_background_image', 'psn_front_image'];

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
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
