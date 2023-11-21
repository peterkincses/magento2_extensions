<?php

declare(strict_types=1);

namespace BAT\Yoti\Plugin;

use BAT\NewsletterExtend\Model\Plugin\CustomerPlugin;
use BAT\Yoti\Helper\Data as YotiHelperData;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Config\Share;
use Magento\Customer\Model\Data\Customer;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Registry;
use Magento\Newsletter\Model\ResourceModel\Subscriber;
use Magento\Newsletter\Model\ResourceModel\Subscriber\CollectionFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Newsletter\Model\SubscriptionManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Psr\Log\LoggerInterface;

class UnsubscribeCustomerPlugin extends CustomerPlugin
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Registry $registry,
        AccountManagementInterface $customerAccountManagement,
        SubscriberFactory $subscriberFactory,
        ExtensionAttributesFactory $extensionFactory,
        CollectionFactory $resourceCollectionFactory,
        SubscriptionManagerInterface $subscriptionManager,
        Share $customerShareConfig,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        Subscriber $subscriberResource,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        parent::__construct(
            $registry,
            $customerAccountManagement,
            $subscriberFactory,
            $extensionFactory,
            $resourceCollectionFactory,
            $subscriptionManager,
            $customerShareConfig,
            $storeManager,
            $logger
        );
    }

    /**
     * Unsubscribe customer when failed yoti age verification
     * @return CustomerInterface|Customer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterSave(CustomerRepository $subject, CustomerInterface $result, CustomerInterface $customer)
    {
        if (!$this->isEnabled()) {
            return parent::afterSave($subject, $result, $customer);
        }

        $isApproved = $result ? $result->getCustomAttribute('is_approved') : null;
        $isApproved = $isApproved ? $isApproved->getValue() : null;

        if ($isApproved === AttributeOptions::NOTAPPROVE) {
            $extAttributes = $customer->getExtensionAttributes();
            if ($extAttributes && $extAttributes->getIsSubscribed()) {
                $extAttributes->setData('is_subscribed', false);
                $customer->setExtensionAttributes($extAttributes);
            }
        }
        return parent::afterSave($subject, $result, $customer);
    }

    /**
     * Directly checking config instead of helper method due to circular dependency error
     */
    private function isEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            YotiHelperData::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );
    }
}
