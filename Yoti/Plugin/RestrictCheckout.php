<?php

declare(strict_types=1);

namespace BAT\Yoti\Plugin;

use BAT\Yoti\Helper\Data as YotiHelper;
use Closure;
use Exception;
use Magento\Checkout\Controller\Index\Index;
use Magento\Customer\Model\Session;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

class RestrictCheckout
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var YotiHelper
     */
    protected $yotiHelper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Session $customerSession,
        RedirectFactory $resultRedirectFactory,
        YotiHelper $yotiHelper,
        LoggerInterface $logger
    ) {
        $this->customerSession = $customerSession;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->yotiHelper = $yotiHelper;
        $this->logger = $logger;
    }

    public function aroundExecute(Index $subject, Closure $proceed): ResultInterface
    {

        if (!$this->yotiHelper->isEnabled() || !$this->yotiHelper->isOldAccountVerifyEnabled()) {
            return $proceed();
        }

        if (!$this->customerSession->isLoggedIn()) {
            return $proceed();
        }

        $isAvRestrictCheckout = false;
        try {
            $customer = $this->yotiHelper->getCustomerById((int) $this->customerSession->getCustomerId());
            $isAvRestrictCheckout =  ($customer) ?
                $this->yotiHelper->isRestrictOldAccountNotVerifiedAvFromCheckout($customer) :
                false;
        } catch (Exception $e) {
            $this->logger->addError('Yoti Restrict Checkout ' . $e->getMessage());
        }

        if ($isAvRestrictCheckout) {
            return $this->resultRedirectFactory->create()->setPath('checkout/cart');
        }

        return $proceed();
    }
}
