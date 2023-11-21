<?php

declare(strict_types=1);

namespace BAT\Yoti\Plugin;

use BAT\Yoti\Helper\Data as YotiHelper;
use BAT\Yoti\Model\Source\YotiApprovalAttributeOptions;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Mageplaza\CustomerApproval\Helper\Data as ApprovalHelper;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;

class PendingYotiVerification
{
    /** @var CustomerRegistry */
    protected $customerRegistry;

    /** @var CustomerResourceModel */
    protected $customerResource;

    /** @var YotiHelper */
    protected $yotiHelper;

    /** @var ApprovalHelper */
    protected $approvalHelper;

    public function __construct(
        CustomerRegistry $customerRegistry,
        CustomerResourceModel $customerResource,
        YotiHelper $yotiHelper,
        ApprovalHelper $approvalHelper
    ) {
        $this->customerResource = $customerResource;
        $this->customerRegistry = $customerRegistry;
        $this->yotiHelper = $yotiHelper;
        $this->approvalHelper = $approvalHelper;
    }

    /**
     * @param int|string $customerId
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundSetApprovePendingById(
        ApprovalHelper $subject,
        callable $proceed,
        $customerId,
        bool $actionRegister
    ): void {
        if ($this->approvalHelper->getIsApproved($customerId) !== AttributeOptions::PENDING) {
            $customer     = $this->customerRegistry->retrieve($customerId);
            $customerData = $customer->getDataModel();

            $customerData->setId($customerId);
            $customerData->setCustomAttribute('is_approved', AttributeOptions::PENDING);
            if ($this->yotiHelper->isEnabledInStore((int) $customer->getStoreId())) {
                $customerData->setCustomAttribute(
                    YotiHelper::YOTI_APPROVED,
                    YotiApprovalAttributeOptions::PENDING
                );
            }
            $customer->updateData($customerData);
            $this->customerResource->save($customer);

            if ($actionRegister) {
                $this->approvalHelper->emailApprovalAction($customer, 'success');
            }
        }
    }
}
