<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Adminhtml\Store;

use BAT\DevicePersonalisation\Helper\Icons;
use Magento\Backend\Block\Store\Switcher as MageStoreSwitcher;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Store\Model\Group;
use Magento\Store\Model\GroupFactory;
use Magento\Store\Model\ResourceModel\Group\Collection;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreFactory;
use Magento\Store\Model\Website;
use Magento\Store\Model\WebsiteFactory;

class Switcher extends MageStoreSwitcher
{

    /**
     * @var Icons
     */
    protected $iconHelper;

    /**
     * @var string
     *
     * phpcs:disable PSR2.Classes.PropertyDeclaration
     */
    protected $_template = 'BAT_DevicePersonalisation::store/switcher.phtml';

    /**
     * @param mixed[] $data
     */
    public function __construct(
        Context $context,
        WebsiteFactory $websiteFactory,
        GroupFactory $storeGroupFactory,
        StoreFactory $storeFactory,
        Icons $iconHelper,
        array $data = []
    ) {
        parent::__construct($context, $websiteFactory, $storeGroupFactory, $storeFactory, $data);
        $this->iconHelper = $iconHelper;
    }

    public function isAdminUser(): bool
    {
        return $this->iconHelper->userHasAllScopes();
    }
}
