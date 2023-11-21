<?php

declare(strict_types=1);

namespace BAT\Yoti\Model\Checkout;

use BAT\Yoti\Helper\Data as YotiHelper;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Cms\Block\Block as CmsBlock;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\View\LayoutInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * @var string
     */
    protected $cmsBlockId;

    /**
     * @var YotiHelper
     */
    protected $yotiHelper;

    /**
     * @var CustomerSession
     */
    protected $customerSession;

    public function __construct(
        LayoutInterface $layout,
        string $blockId,
        YotiHelper $yotiHelper,
        CustomerSession $customerSession
    ) {
        $this->layout = $layout;
        $this->cmsBlockId = $blockId;
        $this->yotiHelper = $yotiHelper;
        $this->customerSession = $customerSession;
    }

    public function getCmsBlockContent(string $blockId): string
    {
        $result = '';

        if (!empty($blockId)) {
            $result = $this->layout->createBlock(CmsBlock::class)->setBlockId($blockId)->toHtml();
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function getConfig()
    {
        $result = [
            'yoti' => [
                'old_account_verification' => [
                    'enabled' => 0,
                ],
            ],
        ];

        $result = $this->getOldAccountNotVerifiedAvNoticeConfig($result);
        return $result;
    }

    protected function getOldAccountNotVerifiedAvNoticeConfig(array $config): array
    {
        if (!$this->customerSession->isLoggedIn()) {
            return $config;
        }

        $customer = $this->yotiHelper->getCustomerById((int) $this->customerSession->getCustomerId());

        if (empty($customer) || !$this->yotiHelper->isShowOldAccountNotVerifiedAvNoticeForCustomer($customer)) {
            return $config;
        }

        $blockContent = !empty($this->cmsBlockId) ? $this->getCmsBlockContent($this->cmsBlockId) : '';
        $config['yoti']['old_account_verification']['enabled'] = 1;
        $config['yoti']['old_account_verification']['noticeContent'] = $blockContent;
        return $config;
    }
}
