<?php

declare(strict_types=1);

namespace BAT\Yoti\Setup\Patch\Data;

use BAT\Yoti\Helper\Data as YotiHelper;
use BAT\Yoti\Model\Source\YotiApprovalAttributeOptions;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Eav\Model\Config;
use Magento\Eav\Model\Entity\Attribute\Set as AttributeSet;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddYotiApprovalCustomerAttribute implements DataPatchInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;

    /**
     * @var Config
     */
    private $eavConfig;

    /**
     * @var AttributeSetFactory
     *
     */
    private $attributeSetFactory;

    /**
     * AddYotiApprovalCustomerAttribute constructor.
     */
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
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerSetup->addAttribute(
            Customer::ENTITY,
            YotiHelper::YOTI_APPROVED,
            [
                'type'             => 'text',
                'input'            => 'select',
                'source'           => YotiApprovalAttributeOptions::class,
                'label'            => 'Yoti Approval Status',
                'visible'          => false,
                'global'           => false,
                'required'         => false,
                'user_defined'     => true,
                'system'           => false,
                'sort_order'       => 1150,
                'position'         => 1150,
                'visible_on_front' => false,
                'default'          => YotiApprovalAttributeOptions::PENDING,
            ]
        );

        $yotiApprovalAttribute = $this->eavConfig
            ->getAttribute(Customer::ENTITY, YotiHelper::YOTI_APPROVED);
        $customerEntity = $customerSetup->getEavConfig()->getEntityType(Customer::ENTITY);
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);

        $yotiApprovalAttribute->addData([
            'attribute_set_id' => $attributeSetId,
            'attribute_group_id' => $attributeGroupId,
        ]);

        $yotiApprovalAttribute->setData(
            'used_in_forms',
            [
                'adminhtml_customer',
            ]
        );

        $yotiApprovalAttribute->save();
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
