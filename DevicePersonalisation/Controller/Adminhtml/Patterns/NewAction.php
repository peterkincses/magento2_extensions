<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Patterns;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class NewAction
 */
class NewAction extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::pattern_create';

    /** @var PageFactory */
    private $resultPageFactory;

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
        $resultPage->setActiveMenu('BAT_DevicePersonalisation::personalisation');
        $resultPage->getConfig()->getTitle()->prepend(__('Pattern'));
        $resultPage->getConfig()->getTitle()->prepend(__('New Pattern'));
        return $resultPage;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
