<?php

declare(strict_types=1);

namespace BAT\Yoti\Controller\Customer;

use BAT\Yoti\Helper\Data as YotiHelper;
use BAT\Yoti\Model\Session as YotiSession;
use BAT\Yoti\Model\Source\YotiApprovalAttributeOptions;
use BAT\Yoti\Model\YotiDocScanSessionFetchResultRequest;
use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\CustomerApproval\Helper\Data as CustomerApprovalHelper;
use Psr\Log\LoggerInterface;
use BAT\Yoti\Model\YotiDocScanMiddlewareSessionRequest;

class YotiDocUpdate extends Action implements HttpPostActionInterface
{
    protected const YOTI_DOC_SCAN_SUCCESS = 'SUCCESS';
    protected const YOTI_DOC_SCAN_ERROR = 'ERROR';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var JsonSerializer
     */
    protected $jsonSerializer;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var YotiHelper
     */
    protected $yotiHelper;

    /**
     * @var YotiSession
     */
    protected $yotiSession;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var YotiDocScanSessionFetchResultRequest
     */
    protected $yotiDocScanSessionFetchResultRequest;

    /**
     * @var CustomerApprovalHelper
     */
    protected $customerApprovalHelper;

    /**
     * @var YotiDocScanMiddlewareSessionRequest
     */
    protected $yotiDocScanMiddlewareSessionRequest;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonSerializer $jsonSerializer,
        LoggerInterface $logger,
        YotiSession $yotiSession,
        CustomerSession $customerSession,
        YotiHelper $yotiHelper,
        YotiDocScanSessionFetchResultRequest $yotiDocScanSessionFetchResultRequest,
        CustomerApprovalHelper $customerApprovalHelper,
        YotiDocScanMiddlewareSessionRequest $yotiDocScanMiddlewareSessionRequest
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
        $this->yotiSession = $yotiSession;
        $this->customerSession = $customerSession;
        $this->yotiHelper = $yotiHelper;
        $this->yotiDocScanSessionFetchResultRequest = $yotiDocScanSessionFetchResultRequest;
        $this->customerApprovalHelper = $customerApprovalHelper;
        $this->yotiDocScanMiddlewareSessionRequest = $yotiDocScanMiddlewareSessionRequest;
        parent::__construct($context);
    }

    public function execute(): ResponseInterface
    {
        $result = [];
        $request = $this->getRequest();
        $successRedirectUrl = null;

        if (
            !$this->yotiHelper->isDocScanEnabled()
            || !$this->yotiSession->hasData('doc_customer_id')
            || !$this->yotiSession->hasData('yoti_doc_scan_sess_id')
            || empty($request->getParam('response'))
        ) {
            $result['error_message'] = __('Internal Error.');
            $result['status'] = 3;
            return $this->jsonResponse($result);
        }

        $response = trim($request->getParam('response'));
        if ($response != self::YOTI_DOC_SCAN_SUCCESS) {
            $result['error_message'] = __('Please try again.');
            $result['status'] = 3;
            return $this->jsonResponse($result);
        }

        $yotiSessionId = $this->yotiSession->getData('yoti_doc_scan_sess_id');
        if (empty($yotiSessionId)) {
            $result['error_message'] = __('Please try uploading your document again.');
            $result['status'] = 3;
            return $this->jsonResponse($result);
        }

        $customerId = (int) $this->yotiSession->getData('doc_customer_id');
        $customer = $this->yotiHelper->getCustomerById($customerId);

        if (!$customer instanceof CustomerInterface) {
            $result['error_message'] = __('Internal Error.');
            $result['status'] = 3;
            return $this->jsonResponse($result);
        }

        $isApproved = $customer->getCustomAttribute('is_approved');
        $isApproved = (!empty($isApproved)) ? $isApproved->getValue() : null;
        $isYotiApproved = $customer->getCustomAttribute(YotiHelper::YOTI_APPROVED);
        $isYotiApproved = (!empty($isYotiApproved)) ? $isYotiApproved->getValue() : null;

        if ($isApproved == 'notapproved' || $isYotiApproved == YotiApprovalAttributeOptions::NOTAPPROVE) {
            $result['error_message'] = __('Please contact Customer Service.');
            $result['status'] = 3;
            $this->yotiSession->clearStorage();
            return $this->jsonResponse($result);
        }

        try {
            $result = $this->yotiDocScanSessionFetchResultRequest->execute($yotiSessionId, $customerId);

            if (isset($result['status']) && $result['status'] === 4) {
                $result['can_retry'] = $this->yotiHelper->canRetryDocScan($customer);

                $this->yotiHelper->updateCustomerRetryDocScan($customer);

                if ($result['can_retry'] === false) {
                    //delete session
                    if ($this->yotiHelper->isDeleteSession()) {
                        $this->yotiDocScanMiddlewareSessionRequest->deleteDocScanSession($yotiSessionId);
                    }
                    $this->yotiSession->clearStorage();
                }

                return $this->jsonResponse($result);
            }

            if (isset($result['status']) && ($result['status'] === 0 || $result['status'] === 1)) {
                $this->yotiHelper->updateCustomer($result['status'], $customer);

                if ($result['status'] === 1) {
                    $this->customerApprovalHelper->emailApprovalAction($customer, 'approve');
                } elseif ($result['status'] === 0) {
                    $this->customerApprovalHelper->emailApprovalAction($customer, 'not_approve');
                    //delete session
                    if ($this->yotiHelper->isDeleteSession()) {
                        $this->yotiDocScanMiddlewareSessionRequest->deleteDocScanSession($yotiSessionId);
                    }
                }
                $successRedirectUrl = $this->yotiSession->getData('referral_url');
                $this->yotiSession->clearStorage();
            } else {
                return $this->jsonResponse($result);
            }

            if (isset($result['status']) && $result['status'] == 0) {
                $result['error_message'] = __(
                    'Sorry, we were not able to verify your age. You will now be redirected from our site in 5 seconds.'
                );
            }

            if ($result['status'] === 1 && $customer->getId() && !$this->customerSession->isLoggedIn()) {
                // force login
                $this->customerSession->loginById($customer->getId());
            } elseif ($result['status'] === 0 && $this->customerSession->isLoggedIn()) {
                // force logout
                $this->customerSession->logout();
            }

            if ($result['status'] === 1) {
                //delete session
                if ($this->yotiHelper->isDeleteSession()) {
                    $this->yotiDocScanMiddlewareSessionRequest->deleteDocScanSession($yotiSessionId);
                }
                $result['redirect'] = $this->_url->getUrl('verification/customer/iframeSuccess');
                if ($successRedirectUrl && $this->yotiHelper->isRedirectToReferralOnAvSuccess($successRedirectUrl)) {
                    $result['redirect'] = $result['redirect'] . '?redirect=' . $successRedirectUrl;
                }
            }
        } catch (Exception $e) {
            $result['status'] = 3;
            $result['error_message'] = $e->getMessage();
        }
        $this->yotiHelper->log('Yoti Doc Scan YotiUpdate Controller response', false, $result);
        return $this->jsonResponse($result);
    }

    /**
     * @param array $response
     * @return mixed
     */
    public function jsonResponse(array $response)
    {
        return $this->getResponse()->representJson(
            $this->jsonSerializer->serialize($response)
        );
    }
}
