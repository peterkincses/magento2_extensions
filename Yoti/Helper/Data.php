<?php

declare(strict_types=1);

namespace BAT\Yoti\Helper;

use BAT\Yoti\Model\Session as YotiSession;
use BAT\Yoti\Model\Source\YotiApprovalAttributeOptions;
use DateTime;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer as CustomerResourceModel;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Url\DecoderInterface;
use Magento\Framework\Url\EncoderInterface;
use Magento\Framework\Validation\ValidationException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mageplaza\CustomerApproval\Model\Config\Source\AttributeOptions as ApprovalAttributeOptions;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    public const YOTI_APPROVED = 'bat_yoti_verification';
    public const XML_PATH_ENABLED = 'age_verification/yoti_age_scan/enabled';
    public const XML_PATH_DEBUG = 'age_verification/yoti_age_scan/debug';
    public const XML_PATH_YOTI_API_URL = 'age_verification/yoti_age_scan/api_url';
    public const XML_PATH_YOTI_AUTH_URL = 'age_verification/yoti_age_scan/auth_url';
    public const XML_PATH_YOTI_AUTH_USERNAME = 'age_verification/yoti_age_scan/auth_username';
    public const XML_PATH_YOTI_AUTH_PASSWORD = 'age_verification/yoti_age_scan/auth_password';
    public const XML_PATH_YOTI_API_TIMEOUT = 'age_verification/yoti_age_scan/api_timeout';
    public const XML_PATH_YOTI_MIN_AGE = 'age_verification/yoti_age_scan/min_age';
    public const XML_PATH_YOTI_UNCERTAINTY = 'age_verification/yoti_age_scan/uncertainty';
    public const XML_PATH_YOTI_ATTEMPTS_ALLOWED = 'age_verification/yoti_age_scan/attempts_allowed';
    public const XML_PATH_YOTI_MAX_FILE_SIZE_ALLOWED = 'age_verification/yoti_age_scan/max_file_size_allowed';
    public const XML_PATH_IS_LIVE = 'age_verification/yoti_age_scan/is_live';
    public const XML_PATH_YOTI_CUSTOMER_REFERENCE = 'age_verification/yoti_age_scan/cutomer_reference';
    public const XML_PATH_YOTI_COUNTRY_CODE = 'age_verification/yoti_age_scan/country_code';
    public const XML_PATH_YOTI_LANGUAGE_CODE = 'age_verification/yoti_age_scan/language_code';
    public const XML_PATH_GENERAL_COUNTRY_DEFAULT = 'general/country/default';
    public const DOC_UPLOAD_PEM_LOCATION_DIR = 'pem/certificates';
    public const XML_PATH_YOTI_DOC_SCAN_ENABLED = 'age_verification/yoti_doc_scan/enabled';
    public const XML_PATH_YOTI_DOC_SCAN_SDK_ID = 'age_verification/yoti_doc_scan/yoti_client_sdk_id';
    public const XML_PATH_YOTI_DOC_SCAN_PEM_KEY = 'age_verification/yoti_doc_scan/pem_key';
    public const XML_PATH_YOTI_COUNTRY_LOCALE = 'age_verification/yoti_doc_scan/locale';
    public const XML_PATH_YOTI_DOC_SCAN_ATTEMPTS_ALLOWED = 'age_verification/yoti_doc_scan/attempts_allowed';
    public const XML_PATH_YOTI_DOC_SCAN_SANDBOX_MODE = 'age_verification/yoti_doc_scan/is_sandbox';
    public const XML_PATH_YOTI_DOC_SCAN_SANDBOX_API_URL = 'age_verification/yoti_doc_scan/sandbpx_api_url';
    public const XML_PATH_YOTI_DOC_SCAN_MIN_AGE = 'age_verification/yoti_doc_scan/min_age';
    public const XML_PATH_YOTI_OLD_NOT_APPROVED_ENABLED = 'age_verification/yoti_old_account_av/enabled';
    public const XML_PATH_YOTI_OLD_NOT_APPROVED_START_DATE = 'age_verification/yoti_old_account_av/start_date';
    public const XML_PATH_YOTI_OLD_NOT_APPROVED_END_DATE = 'age_verification/yoti_old_account_av/end_date';
    public const XML_PATH_YOTI_OLD_NOT_APPROVED_SUBSCRIPTION_PAUSE_ENABLED
        = 'age_verification/yoti_old_account_av/subscription_pause_enabled';
    public const AV_CUTOFF_TIME = '23:59:00';
    public const XML_PATH_YOTI_DOC_SCAN_INTEGRATION_TYPE = 'age_verification/yoti_doc_scan/integration_type';
    public const XML_PATH_YOTI_DOC_SCAN_ENDPOINT_URL = 'age_verification/yoti_doc_scan/end_point_url';
    public const XML_PATH_YOTI_DOC_SCAN_OAUTH_URL = 'age_verification/yoti_doc_scan/oauth_url';
    public const XML_PATH_YOTI_DOC_SCAN_OAUTH_USERNAME = 'age_verification/yoti_doc_scan/oauth_username';
    public const XML_PATH_YOTI_DOC_SCAN_OAUTH_PASSWORD = 'age_verification/yoti_doc_scan/oauth_password';
    public const XML_PATH_YOTI_DOC_SCAN_OAUTH_SCOPE = 'age_verification/yoti_doc_scan/oauth_scope';
    public const XML_PATH_YOTI_DOC_SCAN_OAUTH_SCOPE_READ_SESSION
        = 'age_verification/yoti_doc_scan/read_session_oauth_scope';
    public const XML_PATH_YOTI_DOC_SCAN_OAUTH_SCOPE_READ_CONTENT
        = 'age_verification/yoti_doc_scan/read_content_oauth_scope';
    public const XML_PATH_YOTI_DOC_SCAN_OAUTH_SCOPE_DELETE_SESSION
        = 'age_verification/yoti_doc_scan/delete_session_oauth_scope';
    public const XML_PATH_YOTI_DOC_SCAN_DISABLE_LIVENESS = 'age_verification/yoti_doc_scan/disable_liveness';
    public const XML_PATH_YOTI_DOC_SCAN_DISABLE_FACEMATCH = 'age_verification/yoti_doc_scan/disable_facematch';
    public const XML_PATH_YOTI_DOC_SCAN_ENABLE_NAME_MATCH = 'age_verification/yoti_doc_scan/name_match';
    public const XML_PATH_YOTI_DOC_SCAN_ENABLE_DOB_MATCH = 'age_verification/yoti_doc_scan/dob_match';
    public const XML_PATH_YOTI_DOC_SCAN_IFRAME_URL = 'age_verification/yoti_doc_scan/iframe_url';
    public const XML_PATH_YOTI_DOC_SCAN_SANDBOX_ENDPOINT_URL = 'age_verification/yoti_doc_scan/sandbox_end_point_url';
    public const XML_PATH_YOTI_DOC_SCAN_SANDBOX_OAUTH_URL = 'age_verification/yoti_doc_scan/sandbox_oauth_url';
    public const XML_PATH_YOTI_DOC_SCAN_SANDBOX_OAUTH_USERNAME
        = 'age_verification/yoti_doc_scan/sandbox_oauth_username';
    public const XML_PATH_YOTI_DOC_SCAN_SANDBOX_OAUTH_PASSWORD
        = 'age_verification/yoti_doc_scan/sandbox_oauth_password';
    public const XML_PATH_YOTI_DOC_SCAN_CREATE_SESSION_API = 'age_verification/yoti_doc_scan/create_doc_session';
    public const XML_PATH_YOTI_DOC_SCAN_GET_SESSION_API = 'age_verification/yoti_doc_scan/read_doc_session';
    public const XML_PATH_YOTI_DOC_SCAN_GET_MEDIA_SESSION_API = 'age_verification/yoti_doc_scan/read_media_doc_session';
    public const XML_PATH_YOTI_DOC_SCAN_IS_DELETE_SESSION_API = 'age_verification/yoti_doc_scan/is_delete_session';
    public const XML_PATH_YOTI_DOC_SCAN_DELETE_DOC_SESSION = 'age_verification/yoti_doc_scan/delete_doc_session';
    public const XML_PATH_YOTI_DOC_SCAN_ENABLE_LOGS = 'age_verification/yoti_doc_scan/enable_doc_debug_logs';
    public const XML_PATH_YOTI_DOC_SCAN_COUNTRY_CODE = 'age_verification/yoti_doc_scan/docscan_country_code';
    public const XML_PATH_YOTI_DOC_SCAN_CHARACTERS_MAPPING = 'age_verification/yoti_doc_scan/string_to_match';
    public const XML_PATH_YOTI_DOC_SCAN_DISABLE_ON_REGISTRATION
        = 'age_verification/yoti_doc_scan/disable_on_registration';
    public const ANTI_SPOOFING_FAKE_PREDICTION_VALUE = 'fake';
    public const ANTI_SPOOFING_REAL_PREDICTION_VALUE = 'real';
    public const ANTI_SPOOFING_REAL_UNDETERMINED_VALUE = 'undetermined';

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var CustomerResourceModel
     */
    protected $customerResourceModel;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var null|int
     */
    protected $storeId = null;

    /**
     * @var Json
     */
    protected $json;

    /**
     * @var EncryptorInterface
     */
    protected $encryptor;

    /**
     * @var CustomerRepositoryInterface
     */
    protected $customerRepository;

    /**
     * @var YotiSession
     */
    protected $yotiSession;

    /**
     * @var EncoderInterface
     */
    protected $urlEncoder;

    /**
     * @var DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @var CustomerInterface| null
     */
    protected $customer = null;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @var TimezoneInterface
     */
    protected $timezone;

    /**
     * @var array
     */
    protected $redirectToReferralPathsAfterAvSuccess = [
        'checkout',
        'customer/account',
    ];

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Json $json,
        EncryptorInterface $encryptor,
        CustomerRepositoryInterface $customerRepository,
        YotiSession $yotiSession,
        EncoderInterface $urlEncoder,
        DecoderInterface $urlDecoder,
        CustomerFactory $customerFactory,
        CustomerResourceModel $customerResourceModel,
        CustomerSession $customerSession,
        TimezoneInterface $timezone
    ) {
        $this->logger = $context->getLogger();
        $this->storeManager = $storeManager;
        $this->json = $json;
        $this->customerRepository = $customerRepository;
        $this->yotiSession = $yotiSession;
        $this->urlEncoder = $urlEncoder;
        $this->urlDecoder = $urlDecoder;
        $this->customerFactory = $customerFactory;
        $this->customerResourceModel = $customerResourceModel;
        $this->encryptor = $encryptor;
        $this->customerSession = $customerSession;
        $this->timezone = $timezone;
        parent::__construct($context);
    }

    /**
     * return url
     */
    public function getAPIUrl(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_API_URL, $this->getStoreId());
    }

    /**
     * @return mixed
     */
    public function getConfig(string $configPath, int $storeId = 0)
    {
        return $this->scopeConfig->getValue($configPath, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * Fetch current store id, defaults back to, if none can be retrieved
     */
    private function getStoreId(): int
    {
        if ($this->storeId === null) {
            try {
                $this->storeId = (int) $this->storeManager->getStore()->getId();
            } catch (NoSuchEntityException $exception) {
                $this->logger->critical($exception->getMessage(), ['exception' => $exception]);
            }
        }
        return $this->storeId ?: 0;
    }

    /**
     * return url
     */
    public function getAuthUrl(): string
    {
        return $this->getConfig(self::XML_PATH_YOTI_AUTH_URL, $this->getStoreId());
    }

    /**
     * auth username
     */
    public function getAuthUsername(): string
    {
        return $this->getConfig(self::XML_PATH_YOTI_AUTH_USERNAME, $this->getStoreId());
    }

    /**
     * auth password
     */
    public function getAuthPassword(): string
    {
        return $this->getConfig(self::XML_PATH_YOTI_AUTH_PASSWORD, $this->getStoreId());
    }

    public function isEnabledInStore(int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Returns the mode status
     */
    public function isLive(): int
    {
        return (int) $this->getConfig(self::XML_PATH_IS_LIVE, $this->getStoreId());
    }

    /**
     * Write log File
     */
    public function log(string $message, bool $useSeparator = false, array $context = []): void
    {
        if ($this->isDebugEnabled() || $this->isDocScanDebugEnable()) {
            if ($useSeparator) {
                $this->logger->error(str_repeat('=', 100));
            }
            $this->logger->error($message, $context);
        }
    }

    /**
     * Returns the debug status
     */
    public function isDebugEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_DEBUG,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * DOCSCAN - read Session
     */
    public function isDocScanDebugEnable(): bool
    {
        return (bool) $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_ENABLE_LOGS, $this->getStoreId());
    }

    public function isJson(string $string): bool
    {
        return (json_decode($string) == null) ? false : true;
    }

    public function getApiTimeout(): int
    {
        return (int) $this->getConfig(self::XML_PATH_YOTI_API_TIMEOUT, $this->getStoreId());
    }

    public function getHashedCustomerId(string $customerId): string
    {
        $result = '';
        if (!empty($customerId) && ($encryptedVal = $this->encryptor->encrypt($customerId))) {
            // make url safe
            $result = $this->urlEncoder->encode($encryptedVal);
        }
        return $result;
    }

    /**
     * @throws ValidationException
     */
    public function validateCustomer(RequestInterface $request): void
    {
        $customer = $this->getCustomer($request);

        if (!$customer instanceof CustomerInterface) {
            throw new ValidationException(__('Either your token has expired or customer not found'));
        }

        $isApproved = $customer->getCustomAttribute('is_approved');
        $isApproved = (!empty($isApproved)) ? $isApproved->getValue() : null;
        $isYotiApproved = $customer->getCustomAttribute(self::YOTI_APPROVED);
        $isYotiApproved = (!empty($isYotiApproved)) ? $isYotiApproved->getValue() : null;

        $isOldAccountNotVerifiedAllowed = $this->isShowOldAccountAvNotice();
        $OldAccountAvNotAllowedstatuses = [
            YotiApprovalAttributeOptions::APPROVED,
            YotiApprovalAttributeOptions::MANUALLYAPPROVED,
        ];

        if (
            $isApproved === ApprovalAttributeOptions::NOTAPPROVE ||
            $isYotiApproved === YotiApprovalAttributeOptions::NOTAPPROVE
        ) {
            throw new ValidationException(__('verify your age'));
        } elseif (
            (!$isOldAccountNotVerifiedAllowed && $isApproved === ApprovalAttributeOptions::APPROVED) ||
            ($isOldAccountNotVerifiedAllowed &&
                $isApproved === ApprovalAttributeOptions::APPROVED &&
                in_array($isYotiApproved, $OldAccountAvNotAllowedstatuses)) ||
            $isYotiApproved === YotiApprovalAttributeOptions::APPROVED ||
            $isYotiApproved === YotiApprovalAttributeOptions::MANUALLYAPPROVED
        ) {
            throw new ValidationException(__('already age verified'));
        }
    }

    public function getCustomer(RequestInterface $request): ?CustomerInterface
    {
        if ($this->customer instanceof CustomerInterface) {
            return $this->customer;
        }
        try {
            if ($this->isDoubleOptIn() && $request->getParam('token')) {
                $decryptedCustId = $this->getCustomerIdFromHash($request->getParam('token'));
                $this->yotiSession->setData('customer_id', $decryptedCustId);
            }

            if ($customerId = $this->yotiSession->getData('customer_id')) {
                $this->customer = $this->customerRepository->getById($customerId);
            } elseif ($encryptedCustId = $request->getParam('token')) {
                $decryptedCustId = $this->getCustomerIdFromHash($encryptedCustId);
                $this->customer = $this->customerRepository->getById($decryptedCustId);
            } elseif ($this->isShowOldAccountAvNotice() && $this->customerSession->isLoggedIn()) {
                $this->customer = $this->customerRepository->getById($this->customerSession->getCustomerId());
            }
        } catch (Exception $e) {
            $this->logger->error('Yoti customer fetch error: ' . $e->getMessage());
        }
        return $this->customer;
    }

    /**
     * Check if magento double optin is enabled
     */
    public function isDoubleOptIn(): bool
    {
        return $this->scopeConfig->isSetFlag(
            AccountConfirmation::XML_PATH_IS_CONFIRM,
            ScopeInterface::SCOPE_WEBSITES,
            $this->storeManager->getWebsite()->getId()
        );
    }

    public function getCustomerIdFromHash(string $hash): int
    {
        $result = '';
        if (!empty($hash) && ($urlDecodedVal = $this->urlDecoder->decode($hash))) {
            $result = (int) $this->encryptor->decrypt($urlDecodedVal);
        }
        return $result;
    }

    public function isShowOldAccountAvNotice(): bool
    {
        $result = false;
        if ($this->isEnabled() && $this->isOldAccountNotVerifiedAvAllowed()) {
            return true;
        }
        return $result;
    }

    /**
     * Is module enabled, returns true or false
     */
    public function isEnabled(): bool
    {
        if ($this->isSelfieEnabled() || $this->isDocScanEnabled()) {
            return true;
        }
        return false;
    }

    /**
     * Is selfie is enabled, returns true or false
     */
    public function isSelfieEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Is Doc Scan enabled, returns true or false
     */
    public function isDocScanEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_YOTI_DOC_SCAN_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function isOldAccountNotVerifiedAvAllowed(): bool
    {
        $result = false;

        if (!$this->isOldAccountVerifyEnabled()) {
            return $result;
        }

        if (!empty($this->isOldAccountVerifyStartDate()) && !empty($this->isOldAccountVerifyEndDate())) {
            $currentDate = $this->timezone->date();
            $startDate = $this->timezone->date($this->isOldAccountVerifyStartDate());
            $endDate = $this->timezone->date($this->isOldAccountVerifyEndDate() . ' ' . self::AV_CUTOFF_TIME);

            if (empty($currentDate) || empty($startDate) || empty($endDate)) {
                return $result;
            }

            if (($currentDate >= $startDate && $currentDate <= $endDate) || $currentDate > $endDate) {
                return true;
            }
        }
        return $result;
    }

    /**
     * Is old account AV enabled, returns true or false
     */
    public function isOldAccountVerifyEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_YOTI_OLD_NOT_APPROVED_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Is old account AV start date
     */
    public function isOldAccountVerifyStartDate(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_OLD_NOT_APPROVED_START_DATE, $this->getStoreId());
    }

    /**
     * Is old account AV end date
     */
    public function isOldAccountVerifyEndDate(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_OLD_NOT_APPROVED_END_DATE, $this->getStoreId());
    }

    public function getAgeVerificationStatus(
        float $predAge,
        float $uncertainty,
        int $customerId,
        string $antiSpoofPrediction
    ): int {

        if (
            ($predAge >= 16 && $predAge < 21)
            || $antiSpoofPrediction === self::ANTI_SPOOFING_FAKE_PREDICTION_VALUE
            || $antiSpoofPrediction === self::ANTI_SPOOFING_REAL_UNDETERMINED_VALUE
        ) {
            $this->updateAccStatusDetails(
                $customerId,
                'dropped after face scan'
            );
            return 2;
        }

        $minRequiredAge = $this->getMinAge();
        $allowedUncertainty = $this->getUncertainty();
        if ($predAge >= 21 && $uncertainty > $allowedUncertainty) {
            $this->updateAccStatusDetails(
                $customerId,
                'dropped after face scan'
            );
            return 2;
        } elseif ($predAge >= $minRequiredAge && $antiSpoofPrediction === self::ANTI_SPOOFING_REAL_PREDICTION_VALUE) {
            $this->updateAccStatusDetails(
                $customerId,
                'successful face scan'
            );
            return 1;
        }
        $this->updateAccStatusDetails(
            $customerId,
            'unsuccessful face scan'
        );
        return 0;
    }

    public function updateAccStatusDetails(int $customerId, string $details): void
    {
        try {
            $customer = $this->customerRepository->getById($customerId);
            $customer->setCustomAttribute('bat_yoti_account_status', $details);
            $this->customerRepository->save($customer);
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }

    /**
     * min age
     */
    public function getMinAge(): int
    {
        return (int) $this->getConfig(self::XML_PATH_YOTI_MIN_AGE, $this->getStoreId());
    }

    /**
     * uncertainty
     */
    public function getUncertainty(): int
    {
        return (int) $this->getConfig(self::XML_PATH_YOTI_UNCERTAINTY, $this->getStoreId());
    }

    public function getDocScanAgeVerificationStatus(array $data, int $customerId): int
    {
        $result = 0;
        if (!isset($data['date_of_birth'])) {
            $this->updateAccStatusDetails(
                $customerId,
                'no dob data found'
            );
            return $result;
        }
        $dob = $data['date_of_birth'];
        if (date('Y-m-d', strtotime($dob)) != $dob) {
            return $result;
        }

        // check if DOB match is enabled
        $customerObj = $this->customerRepository->getById($customerId);
        if ($this->isDOBMatchEnabled()) {
            $customerDOB = $customerObj->getDob();
            if ($customerDOB) {
                if (strtotime($dob) != strtotime($customerDOB)) {
                    $this->updateAccStatusDetails(
                        $customerId,
                        'DOB did not match with DOB in account'
                    );
                    return $result;
                }
            }
        }

        $dobDateTime = new DateTime($dob);
        $nowDateTime = new DateTime();
        $dobDateTimeDiff = $nowDateTime->diff($dobDateTime);
        if (!$dobDateTimeDiff) {
            return $result;
        }
        $customerAge = $dobDateTimeDiff->y;
        $minRequiredAge = $this->getDocScanMinAge();
        $result = ($customerAge >= $minRequiredAge) ? 1 : 0;
        $status = ($result === 1) ? 'successful' : 'unsuccessful';
        $this->updateAccStatusDetails(
            $customerId,
            $status . 'Doc scan'
        );

        //check firstname and lastname
        if ($this->isNameMatchEnabled()) {
            $result = 1;
            $customerLastName = $this->sanitizeString(trim($customerObj->getLastname()));
            $docFamilyName = $this->sanitizeString(trim($data['family_name']));
            if (!$docFamilyName) {
                $this->updateAccStatusDetails(
                    $customerId,
                    'Name is not fetched from the document'
                );
                $result = 0;
            } elseif ($customerLastName != $docFamilyName) {
                $this->updateAccStatusDetails(
                    $customerId,
                    'Name did not match with the details in document'
                );
                $result = 0;
            }
        }

        return $result;
    }

    /**
     * DOCSCAN - is DOB Match Enabled
     */
    public function isDOBMatchEnabled(): bool
    {
        return (bool) $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_ENABLE_DOB_MATCH, $this->getStoreId());
    }

    /**
     * min age for yoti doc scan
     */
    public function getDocScanMinAge(): int
    {
        return (int) $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_MIN_AGE, $this->getStoreId());
    }

    /**
     * DOCSCAN - is Name Match Enabled
     */
    public function isNameMatchEnabled(): bool
    {
        return (bool) $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_ENABLE_NAME_MATCH, $this->getStoreId());
    }

    /**
     * sanitize string
     */
    public function sanitizeString(string $string): ?string
    {
        $replace = [];
        $characterMapping = $this->getCharactersMapping();
        if (is_null($characterMapping)) {
            return $string;
        }
        $characterArray = explode('|', $characterMapping);
        foreach ($characterArray as $key => $value) {
            $specialCharacterArray = explode(':', $value);
            $replace[trim($specialCharacterArray[0])] = trim($specialCharacterArray[1]);
        }
        $string = str_replace(array_keys($replace), $replace, $string);
        $string = str_replace(' ', '-', $string);
        $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
        return strtolower(preg_replace('/-+/', '-', $string));
    }

    /**
     * get characters mapping
     */
    public function getCharactersMapping(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_CHARACTERS_MAPPING, $this->getStoreId());
    }

    /**
     * Validate the data against fetched results
     */
    public function getDocScanAgeVerificationMiddlewareStatus(array $data, int $customerId): int
    {
        $result = 0;
        $customerObj = $this->customerRepository->getById($customerId);
        // check if DOB match is enabled
        if ($this->isDOBMatchEnabled()) {
            if (!isset($data['date_of_birth'])) {
                $this->updateAccStatusDetails(
                    $customerId,
                    'no dob data found'
                );
                return $result;
            }
            $dob = $data['date_of_birth'];
            if (date('Y-m-d', strtotime($dob)) != $dob) {
                return $result;
            }
            $customerDOB = $customerObj->getDob();
            if ($customerDOB) {
                if (strtotime($dob) != strtotime($customerDOB)) {
                    $this->updateAccStatusDetails(
                        $customerId,
                        'DOB did not match with DOB in account'
                    );
                    return $result;
                }
            }
            $dobDateTime = new DateTime($dob);
            $nowDateTime = new DateTime();
            $dobDateTimeDiff = $nowDateTime->diff($dobDateTime);
            if (!$dobDateTimeDiff) {
                return $result;
            }
            $customerAge = $dobDateTimeDiff->y;
            $minRequiredAge = $this->getDocScanMinAge();
            $result = ($customerAge >= $minRequiredAge) ? 1 : 0;
            $status = ($result === 1) ? 'successful' : 'unsuccessful';
            $this->updateAccStatusDetails(
                $customerId,
                $status . 'Doc scan'
            );
        }
        //check firstname and lastname
        if ($this->isNameMatchEnabled()) {
            $result = 1;
            $customerLastName = $this->sanitizeString(trim($customerObj->getLastname()));
            $docFamilyName = $this->sanitizeString(trim($data['family_name']));
            if (!$docFamilyName) {
                $this->updateAccStatusDetails(
                    $customerId,
                    'Name is not fetched from the document'
                );
                $result = 0;
            } elseif ($customerLastName != $docFamilyName) {
                $this->updateAccStatusDetails(
                    $customerId,
                    'Name did not match with the details in document'
                );
                $result = 0;
            }
        }

        // both checks are disabled
        if (!$this->isDOBMatchEnabled() && !$this->isNameMatchEnabled()) {
            $result = 1;
        }

        return $result;
    }

    public function getCalculatedAge(array $data): float
    {
        $result = 0;
        if (is_array($data) && isset($data['predAge']) && isset($data['uncertainty'])) {
            $result = (int) ($data['predAge'] - $data['uncertainty']);
        }
        return $result;
    }

    /**
     * cutomer reference
     */
    public function getCustomerReference(): string
    {
        return (string) $this->getConfig(self::XML_PATH_YOTI_CUSTOMER_REFERENCE, $this->getStoreId());
    }

    /**
     * country code
     */
    public function getCountryCode(): string
    {
        $countryCode = (string) $this->getConfig(self::XML_PATH_YOTI_COUNTRY_CODE, $this->getStoreId());
        if ($countryCode != '') {
            return $countryCode;
        }
        return (string) $this->getConfig(self::XML_PATH_GENERAL_COUNTRY_DEFAULT, $this->getStoreId());
    }

    public function updateCustomer(int $status, CustomerInterface $customer): void
    {
        $batYotiVerificationAttrValue = $this->getYotiVerificationStatusForUpdate($status, $customer);
        $isApprovedAttrValue = ($status == 1) ? 'approved' : 'notapproved';

        $customer->setCustomAttribute(self::YOTI_APPROVED, $batYotiVerificationAttrValue);
        $customer->setCustomAttribute('is_approved', $isApprovedAttrValue);
        try {
            $this->customerRepository->save($customer);
            $this->logger->info('Yoti verification is success for customerID = ' . $customer->getId());
        } catch (Exception $e) {
            $this->logger->error('Yoti failed to updated customer on success page ' . $e->getMessage());
        }
    }

    protected function getYotiVerificationStatusForUpdate(int $status, CustomerInterface $customer): string
    {
        $result = YotiApprovalAttributeOptions::NOTAPPROVE;
        $oldIsYotiApproved = $customer->getCustomAttribute(self::YOTI_APPROVED);
        $oldIsYotiApprovedValue = (!empty($oldIsYotiApproved)) ? $oldIsYotiApproved->getValue() : null;

        if ($status == 1) {
            $result = YotiApprovalAttributeOptions::APPROVED;
        } elseif ($status != 1 && $oldIsYotiApprovedValue === YotiApprovalAttributeOptions::NOTCHECKED) {
            $result = YotiApprovalAttributeOptions::OLDNOTAPPROVED;
        }

        return $result;
    }

    public function canRetry(CustomerInterface $customer): bool
    {
        $attempts = $customer->getCustomAttribute('bat_yoti_attempts');
        $attempts = ($attempts !== null) ? (int) $attempts->getValue() : 0;
        $allowedAttempts = $this->getAttemptsAllowed();

        return $attempts < $allowedAttempts;
    }

    public function getAttemptsAllowed(): int
    {
        return (int) $this->getConfig(self::XML_PATH_YOTI_ATTEMPTS_ALLOWED, $this->getStoreId());
    }

    public function canRetryDocScan(CustomerInterface $customer): bool
    {
        $attempts = $customer->getCustomAttribute('bat_yoti_doc_scan_attempts');
        $attempts = ($attempts !== null) ? (int) $attempts->getValue() : 0;
        $allowedAttempts = $this->getYotiDocScanAttemptsAllowed();

        return $attempts < $allowedAttempts;
    }

    public function getYotiDocScanAttemptsAllowed(): int
    {
        return (int) $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_ATTEMPTS_ALLOWED, $this->getStoreId());
    }

    /**
     * Update customer retry attempts for face scan
     */
    public function updateCustomerRetry(CustomerInterface $customer): void
    {
        try {
            $customerModel = $this->customerFactory->create()->load($customer->getId());
            $attempts = (int) $customerModel->getData('bat_yoti_attempts');

            $customerModel->setData('bat_yoti_attempts', ++$attempts);
            $this->customerResourceModel->saveAttribute($customerModel, 'bat_yoti_attempts');
        } catch (Exception $e) {
            $this->logger->error('Yoti failed to updated customer retry attempt' . $e->getMessage());
        }
    }

    /**
     * Update customer retry attempts for doc scan
     */
    public function updateCustomerRetryDocScan(CustomerInterface $customer): void
    {
        try {
            $customerModel = $this->customerFactory->create()->load($customer->getId());
            $attempts = (int) $customerModel->getData('bat_yoti_doc_scan_attempts');

            $customerModel->setData('bat_yoti_doc_scan_attempts', ++$attempts);
            $this->customerResourceModel->saveAttribute($customerModel, 'bat_yoti_doc_scan_attempts');
        } catch (Exception $e) {
            $this->logger->error('Yoti failed to updated customer retry doc scan attempt' . $e->getMessage());
        }
    }

    /**
     * Doc Scan SDK ID
     */
    public function getDocScanSdkId(): ?string
    {
        return (string) $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_SDK_ID, $this->getStoreId());
    }

    /**
     * Doc Scan PEM Key
     */
    public function getDocScanPemKey(): ?string
    {
        return (string) $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_PEM_KEY, $this->getStoreId());
    }

    public function getLocaleForYotiDocScan(): ?string
    {
        return (string) $this->getConfig(self::XML_PATH_YOTI_COUNTRY_LOCALE, $this->getStoreId());
    }

    public function getCustomerById(int $id): ?CustomerInterface
    {
        $result = null;
        if (!empty($id)) {
            try {
                $result = $this->customerRepository->getById($id);
            } catch (Exception $e) {
                $this->_logger->error('Yoti Customer Fetch Error ' . $e->getMessage());
            }
        }
        return $result;
    }

    /**
     * Doc Scan Sandbox API Url
     */
    public function getDocScanSandboxApiUrl(): ?string
    {
        return (string) $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_SANDBOX_API_URL, $this->getStoreId());
    }

    public function isOldAccountVerifyEnabledInStore(int $storeId): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            self::XML_PATH_YOTI_OLD_NOT_APPROVED_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function oldAccountVerifyEndDateByStore(int $storeId): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_OLD_NOT_APPROVED_END_DATE, $storeId);
    }

    public function isShowOldAccountNotVerifiedAvNoticeForCustomer(CustomerInterface $customer): bool
    {
        $result = false;
        if (empty($customer) || !$customer->getId()) {
            return $result;
        }

        $isYotiApproved = $customer->getCustomAttribute(self::YOTI_APPROVED);
        $isYotiApprovedValue = (!empty($isYotiApproved)) ? $isYotiApproved->getValue() : null;
        $isApproved = $customer->getCustomAttribute('is_approved');
        $isApproved = (!empty($isApproved)) ? $isApproved->getValue() : null;

        if (
            $isApproved === ApprovalAttributeOptions::APPROVED &&
            $isYotiApprovedValue === YotiApprovalAttributeOptions::NOTCHECKED &&
            $this->isShowOldAccountAvNotice()
        ) {
            $result = true;
        }

        return $result;
    }

    public function isRestrictOldAccountNotVerifiedAvFromCheckout(CustomerInterface $customer): bool
    {
        $result = false;
        if (empty($customer)) {
            return $result;
        }

        if (!$this->isOldAccountVerifyEnabled()) {
            return $result;
        }

        $isYotiApproved = $customer->getCustomAttribute(self::YOTI_APPROVED);
        $isYotiApprovedValue = (!empty($isYotiApproved)) ? $isYotiApproved->getValue() : null;
        $isApproved = $customer->getCustomAttribute('is_approved');
        $isApproved = (!empty($isApproved)) ? $isApproved->getValue() : null;

        if (
            $isApproved === ApprovalAttributeOptions::APPROVED &&
            $isYotiApprovedValue === YotiApprovalAttributeOptions::NOTCHECKED &&
            $this->isRestrictCheckoutForAgeVerification()
        ) {
            $result = true;
        }

        return $result;
    }

    public function isRestrictCheckoutForAgeVerification(): bool
    {
        $result = false;

        if (!$this->isOldAccountVerifyEnabled()) {
            return $result;
        }

        if (!empty($this->isOldAccountVerifyEndDate())) {
            $currentDate = $this->timezone->date();
            $endDate = $this->timezone->date($this->isOldAccountVerifyEndDate() . ' 23:59:00');

            if ($endDate && $currentDate > $endDate) {
                return true;
            }
        }
        return $result;
    }

    public function getFaceScanMaxAllowedFileSize(): int
    {
        return (int) $this->getConfig(self::XML_PATH_YOTI_MAX_FILE_SIZE_ALLOWED, $this->getStoreId());
    }

    public function isRedirectToReferralOnAvSuccess(string $referral): bool
    {
        $result = false;

        if (empty($referral)) {
            return $result;
        }

        if (!is_array($this->redirectToReferralPathsAfterAvSuccess)) {
            return $result;
        }

        foreach ($this->redirectToReferralPathsAfterAvSuccess as $allowedPath) {
            if (strpos($referral, $allowedPath) !== false && !$this->isCustomerAccountCreatePage($referral)) {
                $result = true;
                break;
            }
        }

        return $result;
    }

    protected function isCustomerAccountCreatePage(string $url): bool
    {
        $result = false;

        if (!empty($url) && strpos($url, 'customer/account/create') !== false) {
            $result = true;
        }

        return $result;
    }

    public function isSubscriptionPauseEnabledInStore(int $storeId): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_YOTI_OLD_NOT_APPROVED_SUBSCRIPTION_PAUSE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function isShowSubscriptionPauseMessage(CustomerInterface $customer): bool
    {
        $result = false;
        if (empty($customer)) {
            return $result;
        }

        if (!$this->isEnabled() || !$this->isSubscriptionPauseEnabled() || !$this->isOldAccountVerifyEnabled()) {
            return $result;
        }

        if ($this->isCustomerApprovedAndNotChecked($customer) && $this->isRestrictCheckoutForAgeVerification()) {
            $result = true;
        }

        return $result;
    }

    public function isSubscriptionPauseEnabled(): bool
    {
        return $this->scopeConfig->isSetFlag(
            self::XML_PATH_YOTI_OLD_NOT_APPROVED_SUBSCRIPTION_PAUSE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    public function isCustomerApprovedAndNotChecked(CustomerInterface $customer): bool
    {
        $result = false;
        if (empty($customer)) {
            return $result;
        }

        $isYotiApproved = $customer->getCustomAttribute(self::YOTI_APPROVED);
        $isYotiApprovedValue = (!empty($isYotiApproved)) ? $isYotiApproved->getValue() : null;
        $isApproved = $customer->getCustomAttribute('is_approved');
        $isApproved = (!empty($isApproved)) ? $isApproved->getValue() : null;

        if (
            $isApproved === ApprovalAttributeOptions::APPROVED &&
            $isYotiApprovedValue === YotiApprovalAttributeOptions::NOTCHECKED
        ) {
            $result = true;
        }

        return $result;
    }

    public function isRestrictSubscriptionUpdate(CustomerInterface $customer): bool
    {
        $result = false;
        if (empty($customer)) {
            return $result;
        }

        if (!$this->isEnabled() || !$this->isSubscriptionPauseEnabled() || !$this->isOldAccountVerifyEnabled()) {
            return $result;
        }

        if ($this->isCustomerApprovedAndNotChecked($customer) && $this->isRestrictCheckoutForAgeVerification()) {
            $result = true;
        }

        return $result;
    }

    /**
     * DOCSCAN - Integration type
     */
    public function getIntegrationType(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_INTEGRATION_TYPE, $this->getStoreId());
    }

    /**
     * DOCSCAN - endpoint url
     */
    public function getEndpointUrl(): ?string
    {
        if ($this->isDocScanSandboxMode()) {
            return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_SANDBOX_ENDPOINT_URL, $this->getStoreId());
        }
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_ENDPOINT_URL, $this->getStoreId());
    }

    /**
     * Returns the doc scan mode status
     */
    public function isDocScanSandboxMode(): bool
    {
        return (bool) $this->scopeConfig->isSetFlag(
            self::XML_PATH_YOTI_DOC_SCAN_SANDBOX_MODE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * DOCSCAN - OAuth url
     */
    public function getOAuthUrl(): ?string
    {
        if ($this->isDocScanSandboxMode()) {
            return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_SANDBOX_OAUTH_URL, $this->getStoreId());
        }
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_OAUTH_URL, $this->getStoreId());
    }

    /**
     * DOCSCAN - OAuth Username
     */
    public function getOAuthUsername(): ?string
    {
        if ($this->isDocScanSandboxMode()) {
            return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_SANDBOX_OAUTH_USERNAME, $this->getStoreId());
        }
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_OAUTH_USERNAME, $this->getStoreId());
    }

    /**
     * DOCSCAN - OAuth Password
     */
    public function getOAuthPassword(): ?string
    {
        if ($this->isDocScanSandboxMode()) {
            return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_SANDBOX_OAUTH_PASSWORD, $this->getStoreId());
        }
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_OAUTH_PASSWORD, $this->getStoreId());
    }

    /**
     * DOCSCAN - OAuth Scope
     */
    public function getOAuthScope(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_OAUTH_SCOPE, $this->getStoreId());
    }

    /**
     * DOCSCAN - OAuth Scope to read session
     */
    public function getOAuthScopeReadSession(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_OAUTH_SCOPE_READ_SESSION, $this->getStoreId());
    }

    /**
     * DOCSCAN - OAuth Scope to read content
     */
    public function getOAuthScopeReadContent(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_OAUTH_SCOPE_READ_CONTENT, $this->getStoreId());
    }

    /**
     * DOCSCAN - OAuth Scope to delete session
     */
    public function getOAuthScopeDeleteSession(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_OAUTH_SCOPE_DELETE_SESSION, $this->getStoreId());
    }

    /**
     * DOCSCAN - is Liveness Disabled
     */
    public function isLivenessDisabled(): bool
    {
        return (bool) $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_DISABLE_LIVENESS, $this->getStoreId());
    }

    /**
     * DOCSCAN - is Facematch Disabled
     */
    public function isFaceMatchDisabled(): bool
    {
        return (bool) $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_DISABLE_FACEMATCH, $this->getStoreId());
    }

    /**
     * DOCSCAN - iFrame url when sandox is disabled
     */
    public function getIframeUrl(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_IFRAME_URL, $this->getStoreId());
    }

    /**
     * DOCSCAN - Create Session
     */
    public function getCreateSessionEndPoint(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_CREATE_SESSION_API, $this->getStoreId());
    }

    /**
     * DOCSCAN - read Session
     */
    public function getReadSessionEndPoint(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_GET_SESSION_API, $this->getStoreId());
    }

    /**
     * DOCSCAN - read Session
     */
    public function getReadMediaSessionEndPoint(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_GET_MEDIA_SESSION_API, $this->getStoreId());
    }

    /**
     * DOCSCAN - read Session
     */
    public function isDeleteSession(): bool
    {
        return (bool) $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_IS_DELETE_SESSION_API, $this->getStoreId());
    }

    /**
     * DOCSCAN - read Session
     */
    public function getDeleteSessionEndPoint(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_DELETE_DOC_SESSION, $this->getStoreId());
    }

    /**
     * DOCSCAN - read Session
     */
    public function getDocScanCountryCode(): ?string
    {
        return $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_COUNTRY_CODE, $this->getStoreId());
    }

    /**
     * Disable DocScan on registratio
     */
    public function isDisableOnRegistration(): bool
    {
        return (bool) $this->getConfig(self::XML_PATH_YOTI_DOC_SCAN_DISABLE_ON_REGISTRATION, $this->getStoreId());
    }

    /**
     * language code
     */
    public function getLanguageCode(): string
    {
        $languageCode = (string) $this->getConfig(self::XML_PATH_YOTI_LANGUAGE_CODE, $this->getStoreId());
        if ($languageCode != '') {
            return $languageCode;
        }
        return 'en';
    }
}
