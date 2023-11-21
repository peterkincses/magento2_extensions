<?php

declare(strict_types=1);

namespace BAT\Yoti\Controller\Customer;

use BAT\Yoti\Helper\Data as YotiHelper;
use BAT\Yoti\Model\Session as YotiSession;
use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\View\Result\PageFactory;

class Age extends Action implements HttpGetActionInterface
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
     * @var YotiSession
     */
    protected $yotiSession;

    /**
     * @var RedirectInterface
     */
    protected $redirect;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        YotiHelper $yotiHelper,
        YotiSession $yotiSession
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->yotiHelper = $yotiHelper;
        $this->yotiSession = $yotiSession;
        $this->redirect = $context->getRedirect();
        parent::__construct($context);
    }

    /**
     * @return ResultInterface|ResponseInterface
     * @throws NotFoundException
     */
    public function execute()
    {
        if (!$this->yotiHelper->isEnabled()) {
            throw new NotFoundException(__('Page not found'));
        }

        try {
            $this->yotiHelper->validateCustomer($this->getRequest());
        } catch (ValidationException $e) {
            /** @var ResultInterface $result */
            $result = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            if ($e->getMessage() == 'verify your age') {
                $this->messageManager
                    ->addErrorMessage(__('Our customer service could not establish that you are over 18.'));
            } elseif ($e->getMessage() == 'already age verified') {
                $this->messageManager
                    ->addNoticeMessage(__('You are already age verified.'));
                return $result->setPath('customer/account/login', ['_secure' => true]);
            } else {
                $this->messageManager
                    ->addErrorMessage(__($e->getMessage()));
            }
            return $result->setPath('');
        }
        $customer = $this->yotiHelper->getCustomer($this->getRequest());
        $this->yotiSession->setData('customer_id', $customer->getId());
        if (!$this->yotiHelper->isSelfieEnabled() && $this->yotiHelper->isDocScanEnabled()) {
            $this->yotiSession->setData('doc_customer_id', $customer->getId());
        }
        if ($referralUrl = $this->redirect->getRefererUrl()) {
            $this->yotiSession->setData('referral_url', $referralUrl);
        }
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Age Verification Page'));

        return $resultPage;
    }
}
