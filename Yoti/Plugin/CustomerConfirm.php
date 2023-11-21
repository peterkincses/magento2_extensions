<?php

declare(strict_types=1);

namespace BAT\Yoti\Plugin;

use BAT\Yoti\Helper\Data as YotiHelper;
use BAT\Yoti\Model\Session as YotiSession;
use Exception;
use Magento\Customer\Controller\Account\Confirm;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;

class CustomerConfirm
{
    public const YOTI_UNVERIFIED_EMAIL_TEMPLATE = 'yoti_email_age_unverified_template';
    public const SENDER_EMAIL = 'trans_email/ident_support/email';
    public const SENDER_NAME = 'trans_email/ident_support/name';

    /**
     * @var YotiSession
     */
    private $yotiSession;

    /**
     * @var YotiHelper
     */
    private $yotiHelper;

    /**
     * @var CustomerRegistry
     */
    private $customerRegistry;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        YotiSession $yotiSession,
        YotiHelper $yotiHelper,
        CustomerRegistry $customerRegistry,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->yotiSession = $yotiSession;
        $this->yotiHelper = $yotiHelper;
        $this->customerRegistry = $customerRegistry;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * If Yoti Double Optin send univerified email and redirect
     */
    public function afterExecute(Confirm $subject, Redirect $result): Redirect
    {
        if (!$this->yotiHelper->isEnabled() || !$this->yotiHelper->isDoubleOptIn()) {
            $this->logger->info('AskQueueIssue Yoti  : is enabled ');
            return $result;
        }

        try {
            /** do nothing if docsan is disabled on registration */
            if ($this->yotiHelper->isDisableOnRegistration()) {
                return $result;
            }
            $customerId = $subject->getRequest()->getParam('id', false);
            if ($customerId) {
                /** @var Customer $mergedCustomerData */
                $customerEmailData = $this->customerRegistry->retrieve($customerId);
                $customerEmailData->setData('name', $customerEmailData->getName());
                $ageVerificationHash = $this->yotiHelper->getHashedCustomerId($customerId);
                $templateVars = ['customer' => $customerEmailData];
                $templateVars['ageVerificationHash'] = $ageVerificationHash;

                $transport = $this->transportBuilder
                    ->setTemplateIdentifier(self::YOTI_UNVERIFIED_EMAIL_TEMPLATE)
                    ->setTemplateOptions([
                        'area' => Area::AREA_FRONTEND,
                        'store' => $customerEmailData->getStoreId(),
                    ])
                    ->setTemplateVars($templateVars)
                    ->setFromByScope($this->getSender((int) $customerEmailData->getStoreId()))
                    ->addTo($customerEmailData->getEmail())
                    ->getTransport();
                $transport->sendMessage();

                $params['token'] = $ageVerificationHash;
                $this->logger->info('AskQueueIssue Yoti  : update customer' . $customerId);
                $result->setPath('verification/customer/age', $params);
            }
        } catch (Exception $e) {
            $this->yotiHelper->log($e->getMessage());
        }

        return $result;
    }

    /**
     * Get sender details for the emaIL
     */
    private function getSender(int $storeId): array
    {
        return [
            'name' => $this->scopeConfig->getValue(self::SENDER_NAME, ScopeInterface::SCOPE_STORE, $storeId),
            'email' => $this->scopeConfig->getValue(self::SENDER_EMAIL, ScopeInterface::SCOPE_STORE, $storeId),
        ];
    }
}
