<?php

declare(strict_types=1);

namespace BAT\Yoti\Controller\Customer;

use BAT\Yoti\Helper\Data as YotiHelper;
use BAT\Yoti\Model\Session as YotiSession;
use BAT\Yoti\Model\Source\YotiApprovalAttributeOptions;
use BAT\Yoti\Model\YotiRequest;
use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Result\PageFactory;
use Mageplaza\CustomerApproval\Helper\Data as CustomerApprovalHelper;
use Psr\Log\LoggerInterface;

class Fetch extends Action implements HttpPostActionInterface
{
    protected const YOTI_FILE_SIZE_LIMIT_KB = 1536;

    protected const YOTI_FILE_MIN_WIDTH = 300;

    protected const YOTI_FILE_MIN_HEIGHT = 300;

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
     * @var array
     */
    protected $error = [];

    /**
     * @var Validator
     */
    protected $formKeyValidator;

    /**
     * @var array
     */
    protected $imageDetails;

    /**
     * @var array
     */
    protected $allowedImageType = ['image/jpeg', 'image/jpg'];

    /**
     * @var YotiRequest
     */
    protected $yotiRequest;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var YotiSession
     */
    protected $yotiSession;

    /**
     * @var CustomerApprovalHelper
     */
    protected $customerApprovalHelper;

    /**
     * @var array
     */
    protected $allowedDeviceType = ['mobile', 'laptop', 'unknown'];

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        JsonSerializer $jsonSerializer,
        LoggerInterface $logger,
        YotiHelper $yotiHelper,
        Validator $formKeyValidator,
        YotiRequest $yotiRequest,
        CustomerSession $customerSession,
        YotiSession $yotiSession,
        CustomerApprovalHelper $customerApprovalHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->logger = $logger;
        $this->yotiHelper = $yotiHelper;
        $this->formKeyValidator = $formKeyValidator;
        $this->yotiRequest = $yotiRequest;
        $this->customerSession = $customerSession;
        $this->yotiSession = $yotiSession;
        $this->customerApprovalHelper = $customerApprovalHelper;
        parent::__construct($context);
    }

    public function execute(): ResponseInterface
    {
        $result = [];
        if (!$this->yotiHelper->isEnabled() || !$this->isvalidRequest()) {
            return $this->jsonResponse($this->error);
        }

        $customer = $this->yotiHelper->getCustomer($this->getRequest());
        if (empty($customer)) {
            return $this->jsonResponse($this->error);
        }

        $isApproved = $customer->getCustomAttribute('is_approved');
        $isApproved = (!empty($isApproved)) ? $isApproved->getValue() : null;
        $isYotiApproved = $customer->getCustomAttribute(YotiHelper::YOTI_APPROVED);
        $isYotiApproved = (!empty($isYotiApproved)) ? $isYotiApproved->getValue() : null;

        if ($isApproved === 'notapproved' || $isYotiApproved === YotiApprovalAttributeOptions::NOTAPPROVE) {
            return $this->jsonResponse(['is_yoti_verified' => 0]);
        }

        try {
            $this->yotiHelper->updateAccStatusDetails(
                (int) $customer->getId(),
                'started face scan'
            );
            $data['data'] = $this->getBase64Data($this->getRequest()->getParam('data'));
            $data['device'] =  $this->getDeviceData($this->getRequest()->getParam('device'));
            $response = $this->yotiRequest->execute($data);
            $response['customerId'] = $customer->getId();
            $result = $this->prepareResponse($response, $customer);

            // update customer only if age prediction was a success
            // if not due to internal error like image size etc, allow upload again
            if (isset($response['predAge'], $response['uncertainty'])) {
                $this->processYotiVerificationResult($customer, $result['is_yoti_verified']);
            }
        } catch (Exception $e) {
            $result = ['code' => 500, 'error_message' => $e->getMessage()];
            $this->logger->critical($e->getMessage());
        }

        if (isset($response['predAge'], $response['uncertainty'], $result['is_yoti_verified'])) {
            $successRedirectUrl = $this->yotiSession->getData('referral_url');
            $this->yotiSession->clearStorage();
            if ($successRedirectUrl) {
                $this->yotiSession->setData('referral_url', $successRedirectUrl);
            }
            $this->yotiSession->setData('yoti_verified_status', $result['is_yoti_verified']);
            $this->yotiSession->setData('yoti_response', $result);

            if ($result['is_yoti_verified'] === 1) {
                $this->yotiSession->setData('success_customer_id', $customer->getId());
                if ($successRedirectUrl && $this->yotiHelper->isRedirectToReferralOnAvSuccess($successRedirectUrl)) {
                    $result['redirect'] = $successRedirectUrl;
                }
            }
        }

        if (isset($result['is_yoti_verified']) && $result['is_yoti_verified'] === 2) {
            $this->yotiSession->setData('doc_customer_id', $customer->getId());
        }

        if (isset($result['is_yoti_verified']) && $result['is_yoti_verified'] === 3) {
            $this->yotiHelper->updateCustomerRetry($customer);
        }

        return $this->jsonResponse($result);
    }

    protected function prepareResponse(array $data, CustomerInterface $customer): array
    {
        $result = [];
        if (
            is_array($data)
            && isset($data['predAge'], $data['uncertainty'], $data['customerId'], $data['anti_spoof_prediction'])
        ) {
            $result['is_yoti_verified'] = $this->yotiHelper->getAgeVerificationStatus(
                (float) $data['predAge'],
                (float) $data['uncertainty'],
                (int) $data['customerId'],
                $data['anti_spoof_prediction']
            );
        } elseif (is_array($data) && isset($data['code']) && $data['code'] === 400) {
            $result['code'] = $data['code'];
            $result['is_yoti_verified'] = 3;
            $result['can_retry'] = $this->yotiHelper->canRetry($customer);
            if (isset($data['error_code'])) {
                $result['error_code'] = $data['error_code'];
            } else {
                $result['error_code'] = 4;
            }
            $result['error_message'] = __($data['error_message']);
        } elseif (is_array($data) && isset($data['code'], $data['error_message'])) {
            $result['code'] = $data['code'];
            $result['error_message'] = __($data['error_message']);
        } else {
            $result['code'] = 500;
            $result['error_message'] = __('Unknown Error.');
            $result['error_message'] .=  ' ' . __('Please retry or contact the customer care team.');
        }
        return $result;
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

    protected function isvalidRequest(): bool
    {
        $request = $this->getRequest();
        if (!$request->isAjax()) {
            $this->error = [];
            return false;
        }

        if (!$this->isValidFormKey()) {
            $this->error = [];
            return false;
        }

        if (!$this->isValidImageType()) {
            $this->error = [
                'code' => 500,
                'error_message' => __('Please use valid image type.'),
            ];
            $this->error['error_message'] .= ' ' . __('Please retry or contact the customer care team.');
            return false;
        }

        if (!$this->isValidImageFileSize()) {
            $this->error = [
                'code' => 500,
                'error_message' => __('Image exceeds maximum allowed size.'),
            ];
            $this->error['error_message'] .= ' ' . __('Please retry or contact the customer care team.');
            return false;
        }

        if (!$this->isValidImageFileDimensions()) {
            $dimension = self::YOTI_FILE_MIN_WIDTH . ' x ' . self::YOTI_FILE_MIN_HEIGHT;
            $this->error = [
                'code' => 500,
                'error_message' => __('Image should have minimum dimension of %1 px.', $dimension),
            ];
            $this->error['error_message'] .= ' ' . __('Please retry or contact the customer care team.');
            return false;
        }

        return true;
    }

    protected function isValidFormKey(): bool
    {
        if (!$this->formKeyValidator->validate($this->getRequest())) {
            return false;
        }
        return true;
    }

    protected function isValidImageType(): bool
    {
        $result = false;
        if (is_array($this->getImageDetails())) {
            if (isset($this->imageDetails['mime']) && !empty($this->imageDetails['mime'])) {
                $mimeType = strtolower($this->imageDetails['mime']);
                return in_array($mimeType, $this->allowedImageType);
            }
        }
        return $result;
    }

    protected function isValidImageFileSize(): bool
    {
        $result = false;
        $image = $this->getRequest()->getParam('data');
        if (empty($image) || !strlen($image) > 0) {
            return $result;
        }
        $sizeInBytes = (int) (strlen(rtrim($image, '=')) * 3 / 4);
        $sizeInKb  = (!empty($sizeInBytes)) ? $sizeInBytes / 1024 : null;
        if (empty($this->yotiHelper->getFaceScanMaxAllowedFileSize())) {
            return true;
        }
        return (!empty($sizeInKb)) ? $sizeInKb <= $this->yotiHelper->getFaceScanMaxAllowedFileSize() : false;
    }

    protected function isValidImageFileDimensions(): bool
    {
        $result = false;
        if (is_array($this->getImageDetails())) {
            if (isset($this->imageDetails[0]) && isset($this->imageDetails[1])) {
                $imageWidth = $this->imageDetails[0];
                $imageHeight = $this->imageDetails[1];
                return ($imageWidth >= self::YOTI_FILE_MIN_WIDTH && $imageHeight >= self::YOTI_FILE_MIN_HEIGHT);
            }
        }
        return $result;
    }

    protected function getImageDetails(): array
    {
        if (empty($this->imageDetails)) {
            $image = $this->getRequest()->getParam('data');
            if (empty($image)) {
                return [];
            }
            $base64Data = $this->getBase64Data($image);
            $imageContent = null;
            if (base64_encode(base64_decode($base64Data, true)) === $base64Data) {
                $imageContent = base64_decode($base64Data, true);
            }
            if (empty($imageContent)) {
                return [];
            }

            $imageDetails = getimagesizefromstring($imageContent);
            if (!is_array($imageDetails)) {
                $imageDetails = [];
            }
            $this->imageDetails = $imageDetails;
            $image = null;
            $imageDetails = null;
        }
        return $this->imageDetails;
    }

    protected function getBase64Data(string $data): string
    {
        $result = '';
        $data = (is_string($data) && strlen($data)) ? explode(',', $data, 2) : null;
        if (is_array($data) && isset($data[1])) {
            return $data[1];
        }
        return $result;
    }

    protected function processYotiVerificationResult(CustomerInterface $customer, int $status): void
    {
        if ($status === 2) {
            return;
        }

        $this->yotiHelper->updateCustomer($status, $customer);

        if ($status === 1) {
            $this->customerApprovalHelper->emailApprovalAction($customer, 'approve');
        } elseif ($status === 0) {
            $this->customerApprovalHelper->emailApprovalAction($customer, 'not_approve');
        }

        if ($status === 1 && $customer->getId() && !$this->customerSession->isLoggedIn()) {
            // force login
            $this->customerSession->loginById($customer->getId());
        } elseif ($status === 0 && $this->customerSession->isLoggedIn()) {
            // force logout
            $this->customerSession->logout();
        }
    }

    protected function getDeviceData(?string $deviceType): string
    {
        if (!empty($deviceType) && in_array(strtolower($deviceType), $this->allowedDeviceType)) {
            return strtolower($deviceType);
        }
        return 'unknown';
    }
}
