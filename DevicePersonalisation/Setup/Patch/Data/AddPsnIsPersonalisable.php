<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddPsnIsPersonalisable implements DataPatchInterface
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
            'psn_is_personalisable',
            [
                'type' => 'int',
                'label' => 'Is Personalisable',
                'input' => 'boolean',
                'used_in_product_listing' => true,
                'user_defined' => true,
                'default' => 0,
                'source' => Boolean::class,
                'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
            ]
        );

        $attributeSets = ['Glo Device', 'Vype Device', 'Glo Tabak Heater'];
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
                'psn_is_personalisable'
            );
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
