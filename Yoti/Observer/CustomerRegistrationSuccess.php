<?php

declare(strict_types=1);

namespace BAT\Yoti\Observer;

use BAT\Yoti\Helper\Data as YotiHelper;
use BAT\Yoti\Model\Session as YotiSession;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CustomerRegistrationSuccess implements ObserverInterface
{
    /**
     * @var YotiHelper
     */
    protected $yotiHelper;

    /**
     * @var YotiSession
     */
    protected $yotiSession;

    /**
     * CustomerRegistrationSuccess constructor.
     */
    public function __construct(
        YotiSession $yotiSession,
        YotiHelper $yotiHelper
    ) {
        $this->yotiSession = $yotiSession;
        $this->yotiHelper = $yotiHelper;
    }
    /**
     * @return $this|void
     */
    public function execute(Observer $observer)
    {
        if (!$this->yotiHelper->isEnabled()) {
            return $this;
        }
        $customer = $observer->getCustomer();
        $customerId = $customer->getId();
        $this->yotiSession->setData('customer_id', $customerId);
        return $this;
    }
}
