<?php

declare(strict_types=1);

namespace BAT\Yoti\Plugin;

use Magento\Newsletter\Model\SubscriberFactory;
use Mageplaza\CustomerApproval\Helper\Data as ApprovalHelperData;

class UnsubscribeNotApproved
{
    /** @var SubscriberFactory */
    protected $subscriberFactory;

    public function __construct(
        SubscriberFactory $subscriberFactory
    ) {
        $this->subscriberFactory = $subscriberFactory;
    }

    /**
     * @param ApprovalHelperData $subject
     * @param mixed $result
     * @param int $customerId
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     * @phpcs:disable
     */
    public function afterNotApprovalCustomerById($subject, $result, $customerId)
    {
        /** @var \Magento\Newsletter\Model\Subscriber $subscriber */
        $subscriber = $this->subscriberFactory->create();
        $subscriber->loadByCustomerId($customerId);
        if ($subscriber->getStatus() === '1') {
            $subscriber->unsubscribe();
        }
    }
}
