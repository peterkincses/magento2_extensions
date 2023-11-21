<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Icon;

use BAT\DevicePersonalisation\Model\PsnIconFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    public function __construct(
        Context $context,
        PsnIconFactory $psnIconFactory,
        Registry $coreRegistry,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->psnIconFactory = $psnIconFactory;
        $this->_coreRegistry = $coreRegistry;
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $id = $this->getRequest()->getParam('icon_id');
        $model = $this->psnIconFactory->create()->load($id);

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Icon no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }

        $this->_coreRegistry->register('personalisation_icon', $model);

        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Icon') : __('New Icon'),
            $id ? __('Edit Icon') : __('New Icon')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Icons'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? $model->getTitle() : __('New Icon'));
        return $resultPage;
    }

    protected function initPage(ResultInterface $resultPage): ResultInterface
    {
        $resultPage->setActiveMenu('BAT_DevicePersonalisation::edit')
            ->addBreadcrumb(__('Icon'), __('Icon'))
            ->addBreadcrumb(__('Manage Icons'), __('Manage Icons'));
        return $resultPage;
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('BAT_DevicePersonalisation::icon_edit');
    }
}
