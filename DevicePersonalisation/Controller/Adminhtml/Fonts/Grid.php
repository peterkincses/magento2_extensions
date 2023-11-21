<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Fonts;

use BAT\DevicePersonalisation\Helper\Icons;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class Grid extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::font';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Icons
     */
    protected $iconHelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager,
        Icons $iconHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
        $this->iconHelper = $iconHelper;
    }

    public function execute(): ResultInterface
    {
        if (!$this->iconHelper->userHasAllScopes()) {
            $roleData = $this->iconHelper->getRoleData();
            if (isset($roleData['gws_stores'])) {
                $this->_redirect('*/*/storeviewindex', ['store' => $roleData['gws_stores'][0]]);
            }
        }
        $storeId = (int) $this->getRequest()->getParam('store');
        $store = $this->storeManager->getStore($storeId);
        $this->storeManager->setCurrentStore($store->getCode());

        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Manage Fonts'), __('Manage Fonts'));
        $resultPage->getConfig()->getTitle()->prepend(__('Fonts'));

        return $resultPage;
    }
}
