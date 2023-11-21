<?php

declare(strict_types=1);

namespace BAT\Yoti\Model;

use Exception;
use Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollection;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class Config
{
    public const XML_PATH_CRON_ENABLED = 'age_verification/accountdelete/enable';
    public const XML_PATH_MONTHS = 'age_verification/accountdelete/accdel';

    /**
     * @var OrderCollection
     */
    private $salesOrderCollection;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var StoreRepositoryInterface
     */

    private $storeRepository;
    /**
     * @var CollectionFactory
     */
    private $systemConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $config;

    /**
     * @var JsonSerializer
     */
    private $serializer;

    public function __construct(
        OrderCollection $salesOrderCollection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CustomerRepositoryInterface $customerRepository,
        ScopeConfigInterface $config,
        CollectionFactory $systemConfig,
        StoreRepositoryInterface $storeRepository,
        JsonSerializer $serializer,
        LoggerInterface $logger
    ) {
        $this->config = $config;
        $this->systemConfig = $systemConfig;
        $this->storeRepository = $storeRepository;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->salesOrderCollection = $salesOrderCollection;
        $this->serializer = $serializer;
    }

    public function isDeleteAccountEnabled(int $websiteId): bool
    {
        $websiteScope = ScopeInterface::SCOPE_WEBSITE;
        $configVal = $this->config->isSetFlag(self::XML_PATH_CRON_ENABLED, $websiteScope, $websiteId);
        return (bool) $configVal;
    }

    public function getDeleteAccountEnabledWebsiteList(): array
    {
        $stores = $this->storeRepository->getList();
        $websiteIds = [];
        foreach ($stores as $store) {
            $websiteId = $store['website_id'];
            if ($this->isDeleteAccountEnabled((int) $websiteId)) {
                array_push($websiteIds, $websiteId);
            }
        }
        return $websiteIds;
    }

    public function getdatafromconfig(string $verificationstatus, int $websiteid): int
    {
        try {
            $websiteScope = ScopeInterface::SCOPE_WEBSITE;
            $configvalue =  $this->config->getValue(self::XML_PATH_MONTHS, $websiteScope, $websiteid);
            $unSerializeData = $this->serializer->unserialize($configvalue);
            foreach ($unSerializeData as $data) {
                if ($data['AV_Status'] == $verificationstatus) {
                    return (int) $data['month'];
                }
            }
        } catch (Exception $exp) {
            $this->logger->error($exp->getMessage(), ['exception' => $exp]);
        }
    }

    public function filterCustomerCreatedAt(string $avStatus, int $websiteid): array
    {
        $customerId = [];
        try {
            $filtermonths = ((int) $this->getdatafromconfig($avStatus, $websiteid));
            $filterDate = date('Y-m-d H:i:s', strtotime('-' . $filtermonths . ' months'));
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('website_id', $websiteid, 'eq')
                ->addFilter('created_at', $filterDate, 'lt')
                ->addFilter('bat_yoti_verification', $avStatus, 'eq')
                ->create();
            $customers = $this->customerRepository->getList($searchCriteria);
            foreach ($customers->getItems() as $customer) {
                $customerId[] = $customer->getId();
            }
        } catch (Exception $exp) {
            $this->logger->error($exp->getMessage(), ['exception' => $exp]);
        }
        return $customerId;
    }

    public function filterCustomerLastOrder(array $array, string $avStatus, int $storeid): array
    {
        $sol = [];
        try {
            $filtermonths = ((int) $this->getdatafromconfig($avStatus, $storeid));
            $filterDate = date('Y-m-d H:i:s', strtotime('-' . $filtermonths . ' months'));
            foreach ($array as $var) {
                $order = $this->salesOrderCollection->create();
                $order->addFieldTofilter('store_id', $storeid);
                $order->addFieldToFilter('customer_id', $var)->setOrder('created_at', 'DESC');
                if ($order->getTotalCount() > 0) {
                    $order->getFirstItem();
                    $orderData = $order->getData();
                    foreach ($orderData as $_order) {
                        $lastdate = $_order['created_at'];
                    }
                    if ($filterDate >= $lastdate) {
                        $sol[] = $var;
                    }
                }
            }
        } catch (Exception $exp) {
            $this->logger->error($exp->getMessage(), ['exception' => $exp]);
        }
        return $sol;
    }
}
