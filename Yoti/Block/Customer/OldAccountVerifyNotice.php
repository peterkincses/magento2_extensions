<?php

declare(strict_types=1);

namespace BAT\Yoti\Block\Customer;

use BAT\Yoti\Helper\Data as YotiHelper;
use Magento\Cms\Block\Block as CmsBlock;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class OldAccountVerifyNotice extends Template
{
    /**
     * @var YotiHelper
     */
    protected $yotiHelper;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    /**
     * @param array $data
     */
    public function __construct(
        YotiHelper $yotiHelper,
        CustomerSession $customerSession,
        Context $context,
        array $data = []
    ) {
        $this->yotiHelper = $yotiHelper;
        $this->customerSession = $customerSession;
        parent::__construct($context, $data);
    }

    public function isShowOldAccountNotVerifiedAvNoticeForCustomer(): bool
    {
        if (!$this->customerSession->isLoggedIn()) {
            return false;
        }
        $customer = $this->yotiHelper->getCustomerById((int) $this->customerSession->getCustomerId());
        return ($customer) ? $this->yotiHelper->isShowOldAccountNotVerifiedAvNoticeForCustomer($customer) : false;
    }

    public function getOldAccountNotVerifiedNoticeContent(): string
    {
        $cmsBlockId = $this->getData('cmsBlockId');
        if (empty($cmsBlockId)) {
            return '';
        }
        return $this->_layout->createBlock(CmsBlock::class)->setBlockId($cmsBlockId)->toHtml();
    }
}
