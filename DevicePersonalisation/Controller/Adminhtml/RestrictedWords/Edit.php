<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\RestrictedWords;

use BAT\DevicePersonalisation\Model\PsnRestrictedWordsFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page as ResultPage;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var PsnRestrictedWordsFactory
     */
    private $psnRestrictedWordsFactory;

    /**
     * @var Magento\Framework\Registry
     */
    private $registry;

    public function __construct(
        Context $context,
        PsnRestrictedWordsFactory $psnRestrictedWordsFactory,
        Registry $registry,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->psnRestrictedWordsFactory = $psnRestrictedWordsFactory;
        $this->registry = $registry;

        parent::__construct($context);
    }

    /**
     * phpcs:disable PSR2.Methods.MethodDeclaration
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('BAT_DevicePersonalisation::restricted_word_edit');
    }
    // phpcs:enable

    public function execute(): ResultInterface
    {
        $id = $this->getRequest()->getParam('word_id');
        $model = $this->psnRestrictedWordsFactory->create()->load($id);

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('The restricted word no longer exists'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->registry->register('personalisation_word', $model);

        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage, (bool) $id);
        $resultPage->getConfig()->getTitle()->prepend(__('Word'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getRestrictedWord() : __('New Word'));

        return $resultPage;
    }

    private function initPage(ResultPage $resultPage, bool $isEdit): ResultPage
    {
        $resultPage->setActiveMenu('BAT_DevicePersonalisation::restricted_word')
            ->addBreadcrumb(__('Word'), __('Word'))
            ->addBreadcrumb(__('Manage Word'), __('Manage Word'))
            ->addBreadcrumb(
                $isEdit ? __('Edit Word') : __('New Word'),
                $isEdit ? __('Edit Word') : __('New Word')
            );

        return $resultPage;
    }
}
