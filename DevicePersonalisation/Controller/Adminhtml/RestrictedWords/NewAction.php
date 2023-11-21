<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\RestrictedWords;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;

class NewAction extends Action
{
    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        ForwardFactory $resultForwardFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;

        parent::__construct($context);
    }

    /**
     * phpcs:disable PSR2.Methods.MethodDeclaration
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('BAT_DevicePersonalisation::restricted_word_create');
    }
    // phpcs:enable

    public function execute(): ResultInterface
    {
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        return $resultForward->forward('edit');
    }
}
