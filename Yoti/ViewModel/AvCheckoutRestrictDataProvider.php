<?php

declare(strict_types=1);

namespace BAT\Yoti\ViewModel;

use BAT\Yoti\Helper\Data as YotiHelper;
use Exception;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Block\ArgumentInterface;

class AvCheckoutRestrictDataProvider implements ArgumentInterface
{

    /**
     * @var YotiHelper
     */
    protected $yotiHelper;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    public function __construct(
        YotiHelper $yotiHelper,
        CustomerSession $customerSession
    ) {
        $this->yotiHelper = $yotiHelper;
        $this->customerSession = $customerSession;
    }

    public function isRestrictOldAccountNotVerifiedAvFromCheckout(): bool
    {
        $result = false;
        if (!$this->customerSession->isLoggedIn()) {
            return $result;
        }

        try {
            $customer = $this->yotiHelper->getCustomerById((int) $this->customerSession->getCustomerId());
            $result =  ($customer) ?
                $this->yotiHelper->isRestrictOldAccountNotVerifiedAvFromCheckout($customer) :
                false;
        } catch (Exception $e) {
            return $result;
        }

        return $result;
    }
}
