<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Fonts;

use BAT\DevicePersonalisation\Api\PsnFontsRepositoryInterface;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;

class Edit extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::font_edit';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var PsnFontsRepositoryInterface
     */
    private $repository;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        PsnFontsRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->repository = $repository;
    }

    public function execute(): ResultInterface
    {
        $id = $this->getRequest()->getParam('font_id');
        return $id === null ? $this->newFont() : $this->editFont((int) $id);
    }

    private function newFont(): ResultInterface
    {
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('New Font'));
        return $resultPage;
    }

    private function editFont(int $id): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $model = $this->repository->getById($id);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath('*/*/');
        }
        $formData = $this->_getSession()->getFormData(true);
        if (!empty($formData)) {
            $model->setData($formData);
        }
        $model->setId($id); // As this field overrided by setData
        $this->registry->register('font_model', $model);
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Font: %1 (%2)', $model->getName(), $id));
        return $resultPage;
    }

    private function createPage(): ResultInterface
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Manage Fonts'), __('Manage Fonts'));
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Font'));
        return $resultPage;
    }
}
