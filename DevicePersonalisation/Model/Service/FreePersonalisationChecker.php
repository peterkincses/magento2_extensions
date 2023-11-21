<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Service;

use BAT\DevicePersonalisation\Helper\Data as Helper;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\CustomerSegment\Helper\Data as CustomerSegmentHelper;
use Magento\CustomerSegment\Model\Customer as CustomerSegment;
use Magento\Framework\App\Http\Context as HttpContext;

class FreePersonalisationChecker
{
    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var CustomerSegment
     */
    private $customerSegment;

    public function __construct(
        Helper $helper,
        HttpContext $httpContext,
        CustomerSession $customerSession,
        CustomerSegment $customerSegment
    ) {
        $this->helper = $helper;
        $this->customerSegment = $customerSegment;
        $this->customerSession = $customerSession;
        $this->httpContext = $httpContext;
    }

    /**
     * Check the given customer is eligible for free personalisation
     */
    public function isCustomerEligible(): bool
    {
        return $this->helper->isFreeEngravingEnabled()
            && ($eligibleCustomerSegmentsIds = $this->helper->getFreeEngravingCustomerSegmentIds())
            && ($customerSegmentIds = $this->getCurrentCustomerSegmentIds())
            && array_filter(array_intersect($eligibleCustomerSegmentsIds, $customerSegmentIds));
    }

    /**
     * Get current customer segment ids
     */
    private function getCurrentCustomerSegmentIds(): array
    {
        $result = $this->httpContext->getValue(CustomerSegmentHelper::CONTEXT_SEGMENT);
        if (
            !is_array($result)
            && ($customer = $this->customerSession->getCustomer())
            && $customer->getId()
        ) {
            $result = $this->customerSegment->getCustomerSegmentIdsForWebsite(
                $customer->getId(),
                $customer->getWebsiteId()
            );
        }

        return is_array($result) ? $result : [];
    }
}
