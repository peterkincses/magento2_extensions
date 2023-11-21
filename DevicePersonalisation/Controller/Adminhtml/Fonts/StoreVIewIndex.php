<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Fonts;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

class StoreVIewIndex extends Action
{
    /**
     * @var PageFactory
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
        $resultPage->addBreadcrumb(__('Manage Fonts'), __('Manage Fonts'));
        $resultPage->getConfig()->getTitle()->prepend(__('Fonts'));
        return $resultPage;
    }
}
