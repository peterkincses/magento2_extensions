<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class PsnTextCoordinateAttrGloHyperIT implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var EavSetupFactory */
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
        $sortOrder = 0;
        $attributeSet = 'Glo Device';
        $groupId = 'Personalisation';

        $psnTextAttributes = [
            'psn_text_front_top_coord' => 'Front Text Coordinates From Top',
            'psn_text_front_left_coord' => 'Front Text Coordinates From Left',
            'psn_text_back_top_coord' => 'Back Text Coordinates From Top',
            'psn_text_back_left_coord' => 'Back Text Coordinates From Left',
        ];

        foreach ($psnTextAttributes as $attrId => $attrLabel) {
            $eavSetup->addAttribute(
                Product::ENTITY,
                $attrId,
                [
                    'type' => 'int',
                    'label' => $attrLabel,
                    'input' => 'text',
                    'user_defined' => true,
                    'default' => 0,
                    'global' => ScopedAttributeInterface::SCOPE_WEBSITE,
                    'required' => false,
                    'frontend_class' => 'validate-number',
                    'searchable' => false,
                    'filterable' => false,
                    'comparable' => false,
                    'unique' => false,
                    'visible_on_front' => false,
                    'used_in_product_listing' => false,
                    'visible' => true,
                ]
            );

            $eavSetup->addAttributeToSet(
                Product::ENTITY,
                $attributeSet,
                $groupId,
                $attrId,
                $sortOrder += 10
            );
        }
    }

    /**
     * @inheritDoc
     */
    public static function getDependencies(): array
    {
        return [CreatePersonalisationAttributes::class];
    }

    /**
     * @inheritDoc
     */
    public function getAliases(): array
    {
        return [];
    }
}
