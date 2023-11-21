<?php

declare(strict_types=1);

namespace BAT\Yoti\Setup\Patch\Data;

use BAT\Yoti\Helper\Data as YotiHelper;
use BAT\Yoti\Model\Source\YotiApprovalAttributeOptions;
use Magento\Customer\Model\Customer;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateYotiApprovedCustomerAttribute implements DataPatchInterface
{
    /** @var ModuleDataSetupInterface */
    private $moduleDataSetup;

    /** @var CustomerSetupFactory */
    private $customerSetupFactory;

    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        CustomerSetupFactory $customerSetupFactory
    ) {
        $this->moduleDataSetup      = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    public function apply(): void
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerSetup->updateAttribute(
            Customer::ENTITY,
            YotiHelper::YOTI_APPROVED,
            'default_value',
            YotiApprovalAttributeOptions::NOTCHECKED
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [AddYotiApprovalCustomerAttribute::class];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
