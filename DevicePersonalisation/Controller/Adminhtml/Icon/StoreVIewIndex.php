<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Icon;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class StoreVIewIndex extends Action
{
    /**
     * @var Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    public function execute(): ResultInterface
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Icon List')));
        return $resultPage;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('BAT_DevicePersonalisation::icon');
    }
}
