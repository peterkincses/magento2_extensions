<?php

declare(strict_types=1);

namespace BAT\Yoti\Controller\Customer;

use BAT\Yoti\Helper\Data as YotiHelper;
use BAT\Yoti\Model\Session as YotiSession;
use BAT\Yoti\Model\Source\YotiApprovalAttributeOptions;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

class Success extends Action implements HttpGetActionInterface
{

    /**
     * @var YotiHelper
     */
    protected $yotiHelper;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var YotiSession
     */
    protected $yotiSession;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        YotiHelper $yotiHelper,
        CustomerSession $customerSession,
        YotiSession $yotiSession,
        CustomerRepositoryInterface $customerRepository,
        LoggerInterface $logger,
        DataPersistorInterface $dataPersistor
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->yotiHelper = $yotiHelper;
        $this->customerSession = $customerSession;
        $this->yotiSession = $yotiSession;
        $this->customerRepository = $customerRepository;
        $this->logger = $logger;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * @return ResultInterface|ResponseInterface
     */
    public function execute()
    {
        if (!$this->yotiHelper->isEnabled()) {
            return $this->_redirect('');
        }

        if ($this->customerSession->isLoggedIn() && !$this->yotiSession->hasData('yoti_verified_status')) {
            return $this->_redirect('customer/account');
        }

        if (
            !$this->yotiSession->hasData('yoti_verified_status')
            && !$this->yotiSession->hasData('success_customer_id')
        ) {
            return $this->_redirect('');
        }

        $customerId = (int) $this->yotiSession->getData('success_customer_id');
        $customer = $this->yotiHelper->getCustomerById($customerId);

        if (!$customer instanceof CustomerInterface) {
            // redirect to login page
            return $this->_redirect('customer/account/login');
        }

        $isApproved = $customer->getCustomAttribute('is_approved');
        $isApproved = (!empty($isApproved)) ? $isApproved->getValue() : null;
        $isYotiApproved = $customer->getCustomAttribute(YotiHelper::YOTI_APPROVED);
        $isYotiApproved = (!empty($isYotiApproved)) ? $isYotiApproved->getValue() : null;

        if ($isApproved == 'notapproved' || $isYotiApproved == YotiApprovalAttributeOptions::NOTAPPROVE) {
            return $this->_redirect('');
        }

        $this->dataPersistor->set('yoti_verified_status', $this->yotiSession->getData('yoti_verified_status'));
        $this->dataPersistor->set('yoti_response', $this->yotiSession->getData('yoti_response'));
        $this->yotiSession->clearStorage();

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Age Verification Success Page.'));
        return $resultPage;
    }
}
