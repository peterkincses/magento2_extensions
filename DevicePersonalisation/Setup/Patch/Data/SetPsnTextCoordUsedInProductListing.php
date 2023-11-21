<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Setup\Patch\Data;

use Magento\Catalog\Model\Product;
use Magento\Eav\Setup\EavSetup;
use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class SetPsnTextCoordUsedInProductListing implements DataPatchInterface
{

    private ModuleDataSetupInterface $moduleDataSetup;

    private EavSetupFactory $eavSetupFactory;

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
        $psnTextAttributes = [
            'psn_text_front_top_coord',
            'psn_text_front_left_coord',
            'psn_text_back_top_coord',
            'psn_text_back_left_coord',
        ];
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);

        foreach ($psnTextAttributes as $attrCode) {
            $attributeId = $eavSetup->getAttribute($entityTypeId, $attrCode, 'attribute_id');
            $eavSetup->updateAttribute(
                $entityTypeId,
                $attributeId,
                [
                    'entity_type_id' => $entityTypeId,
                    'attribute_code' => $attrCode,
                    'used_in_product_listing' => true,
                ],
            );
        }
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
