<?php

declare(strict_types=1);

namespace BAT\Yoti\Setup\Patch\Data;

use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddAttemptsAllowed implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var CustomerSetupFactory */
    private $customerSetupFactory;

    /** @var Config */
    private $eavConfig;

    /** @var AttributeSetFactory */
    private $attributeSetFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory,
        Config $eavConfig,
        AttributeSetFactory $attributeSetFactory
    ) {
        $this->moduleDataSetup      = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->eavConfig            = $eavConfig;
        $this->attributeSetFactory  = $attributeSetFactory;
    }

    public function apply(): void
    {
        $attemptAttributes = [
            'bat_yoti_attempts' => [
                'label' => 'Yoti Attempts',
            ],
            'bat_yoti_doc_scan_attempts' => [
                'label' => 'Yoti Doc Scan Attempts',
            ],
        ];

        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        foreach ($attemptAttributes as $code => $opt) {
            $customerSetup->addAttribute(
                Customer::ENTITY,
                $code,
                [
                    'type'             => 'int',
                    'input'            => 'text',
                    'label'            => $opt['label'],
                    'visible'          => true,
                    'required'         => false,
                    'user_defined'     => true,
                    'system'           => false,
                    'default'          => '0',
                ]
            );

            $yotiAttribute = $this->eavConfig->getAttribute(Customer::ENTITY, $code);
            $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
            $attributeSetId = $customerEntity->getDefaultAttributeSetId();

            /** @var $attributeSet AttributeSet */
            $attributeSet = $this->attributeSetFactory->create();
            $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

            $yotiAttribute->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
            ]);

            $yotiAttribute->save();
        }
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
