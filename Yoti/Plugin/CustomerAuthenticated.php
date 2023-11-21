<?php

declare(strict_types=1);

namespace BAT\Yoti\Plugin;

use BAT\CustomerApprovalExtend\Model\Config as ApprovalConfig;
use BAT\Yoti\Helper\Data as YotiHelper;
use BAT\Yoti\Model\Session as YotiSession;
use Closure;
use Magento\Customer\Model\AccountManagement;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CusCollectFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\CustomerApproval\Helper\Data as HelperData;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions;
use Mageplaza\CustomerApproval\Model\Config\Source\TypeNotApprove;

/**
 * Class CustomerAuthenticated
 */
class CustomerAuthenticated
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ResponseInterface
     */
    protected $response;

    /**
     * @var CusCollectFactory
     */
    protected $cusCollectFactory;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CustomerAuthenticated constructor.
     */
    public function __construct(
        HelperData $helperData,
        ManagerInterface $messageManager,
        ResponseFactory $response,
        CusCollectFactory $cusCollectFactory,
        Session $customerSession,
        RedirectInterface $redirect,
        StoreManagerInterface $storeManager,
        YotiSession $yotiSession,
        YotiHelper $yotiHelper,
        ApprovalConfig $approvalConfig
    ) {
        $this->helperData         = $helperData;
        $this->messageManager     = $messageManager;
        $this->response           = $response;
        $this->cusCollectFactory  = $cusCollectFactory;
        $this->customerSession    = $customerSession;
        $this->redirect           = $redirect;
        $this->storeManager       = $storeManager;
        $this->yotiSession        = $yotiSession;
        $this->yotiHelper         = $yotiHelper;
        $this->approvalConfig     = $approvalConfig;
    }

    /**
     *
     * @return                   mixed
     * @throws                   InputException
     * @throws                   LocalizedException
     * @throws                   NoSuchEntityException
     * @throws                   FailureToSendException
     * @SuppressWarnings(Unused)
     */
    public function aroundAuthenticate(
        AccountManagement $subject,
        Closure $proceed,
        string $username,
        string $password
    ) {
        $result = $proceed($username, $password);
        if (!$this->helperData->isEnabled()) {
            return $result;
        }

        $websiteId      = $this->storeManager->getStore()->getWebsiteId();
        $storeId        = $this->storeManager->getStore()->getId();
        $customerFilter = $this->cusCollectFactory->create()
            ->addFieldToFilter('email', $username)
            ->addFieldToFilter('website_id', $websiteId)
            ->getFirstItem();

        // check old customer and set approved
        $getIsApproved = null;

        if ($customerId = $customerFilter->getId()) {
            // check new customer logedin
            $getIsApproved = $this->helperData->getIsApproved($customerId);
        }

        if ($customerId && $getIsApproved !== AttributeOptions::APPROVED && !empty($getIsApproved)) {
            // case redirect
            $urlRedirect = $this->helperData->getUrl($this->helperData->getCmsRedirectPage(), ['_secure' => true]);
            if (
                $this->helperData->getTypeNotApprove() === TypeNotApprove::SHOW_ERROR
                || empty($this->helperData->getTypeNotApprove())
            ) {
                // Redirect to login screen
                $urlRedirect = $this->helperData->getUrl('customer/account/login', ['_secure' => true]);
                $this->messageManager->addErrorMessage(__($this->helperData->getErrorMessage()));
            }

            if ($getIsApproved === AttributeOptions::PENDING) {
                //case - redirect to custom URL set in Configuration
                $customUrlRedirect = $this->approvalConfig->getRedirectUrl((int) $storeId);
                if (trim($customUrlRedirect) != '') {
                    $urlRedirect = $this->helperData->getUrl($customUrlRedirect, ['_secure' => true]);
                }
            }

            // force logout customer
            $this->customerSession->logout()
                ->setBeforeAuthUrl($this->redirect->getRefererUrl())
                ->setLastCustomerId($customerId);

            // processCookieLogout
            $this->helperData->processCookieLogout();

            //yoti - session id
            if ($this->yotiHelper->isEnabled()) {
                $this->yotiSession->setData('customer_id', $customerId);
            }

            // force redirect
            return $this->response->create()->setRedirect($urlRedirect)->sendResponse();
        }

        return $result;
    }
}
