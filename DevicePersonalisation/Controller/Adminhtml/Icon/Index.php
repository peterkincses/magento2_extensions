<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Icon;

use BAT\DevicePersonalisation\Helper\Icons;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var Icons
     */
    protected $iconHelper;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Icons $iconHelper
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
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
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Icon List')));
        return $resultPage;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('BAT_DevicePersonalisation::icon');
    }
}
