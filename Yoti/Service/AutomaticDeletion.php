<?php

declare(strict_types=1);

namespace BAT\Yoti\Service;

use BAT\Yoti\Model\Config;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class AutomaticDeletion
{
    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        Registry $registry,
        LoggerInterface $logger,
        Config $config,
        StoreManagerInterface $storeManager
    ) {
        $this->customerRepository = $customerRepository;
        $this->registry = $registry;
        $this->logger = $logger;
        $this->config = $config;
        $this->storeManager = $storeManager;
    }

    public function execute(array $websiteIds): void
    {
        foreach ($websiteIds as $websiteid) {
            $this->logger->info('Cronjob AccountDelete: for website ID ' . $websiteid);
            $this->logger->info('Cronjob AccountDelete: collection filtering start');
            $storeId = $this->storeManager->getWebsite($websiteid)->getDefaultStore()->getId();
            $cusnotchecked = $this->config->filterCustomerCreatedAt('notchecked', (int) $websiteid);
            $cusapproved = $this->config->filterCustomerCreatedAt('approved', (int) $websiteid);
            $notcheckedid = $this->config->filterCustomerLastOrder($cusnotchecked, 'notchecked', (int) $storeId);
            $approvedid = $this->config->filterCustomerLastOrder($cusapproved, 'approved', (int) $storeId);
            $notapprovedid = $this->config->filterCustomerCreatedAt('notapproved', (int) $websiteid);
            $oldnotapprovedid = $this->config->filterCustomerCreatedAt('oldnotapproved', (int) $websiteid);
            $pendingdid = $this->config->filterCustomerCreatedAt('pending', (int) $websiteid);
            $this->logger->info('Cronjob AccountDelete: collection filtering end');
            $customers = array_merge($notcheckedid, $approvedid, $notapprovedid, $oldnotapprovedid, $pendingdid);
            $this->logger->info('Cronjob AccountDelete: Total customers to delete: ' . count($customers));
            if (count($customers)) {
                foreach ($customers as $customer) {
                    $this->logger->info('Cronjob AccountDelete: Deleting customer ID: ' . $customer);
                    $this->deleteCustomerById((int) $customer);
                    $this->logger->info('Cronjob AccountDelete: Deleted successfully customer ID: ' . $customer);
                }
            }
        }
    }

    private function deleteCustomerById(int $customerId): void
    {
        try {
            /** When perform delete operation, magento check isSecureArea is true. */
            $this->registry->register('isSecureArea', true);
            $this->customerRepository->deleteById($customerId);
            $this->registry->unregister('isSecureArea');
        } catch (Exception $e) {
            $this->logger->error(
                __('Something went wrong while deleting account'),
                [
                    'customer_id' => $customerId,
                    'exception' => $e,
                ]
            );
        }
    }
}
