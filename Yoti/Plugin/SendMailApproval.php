<?php

declare(strict_types=1);

namespace BAT\Yoti\Plugin;

use BAT\Yoti\Helper\Data as YotiHelperData;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\TransportBuilder;
use Mageplaza\CustomerApproval\Helper\Data as ApprovalHelperData;
use Psr\Log\LoggerInterface;

class SendMailApproval
{
    /** @var YotiHelperData */
    private $yotiHelper;

    /** @var CustomerRegistry */
    private $customerRegistry;

    /** @var TransportBuilder */
    private $transportBuilder;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        YotiHelperData $yotiHelper,
        CustomerRegistry $customerRegistry,
        TransportBuilder $transportBuilder,
        LoggerInterface $logger
    ) {
        $this->yotiHelper = $yotiHelper;
        $this->customerRegistry = $customerRegistry;
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
    }

    /**
     * @param string|array<mixed> $sender
     */
    public function aroundSendMail(
        ApprovalHelperData $subject,
        callable $proceed,
        string $sendTo,
        object $customer,
        string $emailTemplate,
        ?int $storeId,
        $sender
    ): bool {
        if (!$this->yotiHelper->isEnabled()) {
            return $proceed($sendTo, $customer, $emailTemplate, $storeId, $sender);
        }

        /** prevent email being sent out if magento confirmation is required */
        if ($this->yotiHelper->isDoubleOptIn() && $emailTemplate === 'yoti_email_age_unverified_template') {
            return false;
        }

        try {
            /** @var Customer $mergedCustomerData */
            $customerEmailData = $this->customerRegistry->retrieve($customer->getId());
            $customerEmailData->setData('name', $customerEmailData->getName());
            $templateVars = ['customer' => $customerEmailData];

            if ($emailTemplate === 'yoti_email_age_unverified_template') {
                $templateVars['ageVerificationHash'] = $this->yotiHelper->getHashedCustomerId($customer->getId());
            }

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions([
                    'area'  => Area::AREA_FRONTEND,
                    'store' => $storeId,
                ])
                ->setTemplateVars($templateVars)
                ->setFrom($sender)
                ->addTo($sendTo)
                ->getTransport();
            $transport->sendMessage();

            return true;
        } catch (Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return false;
    }
}
