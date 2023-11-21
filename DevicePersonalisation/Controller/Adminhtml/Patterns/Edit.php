<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Patterns;

use BAT\DevicePersonalisation\Api\PsnPatternsRepositoryInterface;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Result\Page;
use Magento\Framework\Controller\ResultInterface;
use Exception;

/**
 * Class Edit
 */
class Edit extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::pattern_edit';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var PsnPatternsRepositoryInterface
     */
    private $repository;


    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        Registry $registry,
        PsnPatternsRepositoryInterface $repository
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->registry = $registry;
        $this->repository = $repository;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('pattern_id');
        return $id === null ? $this->newPattern() : $this->editPattern($id);
    }

    /**
     * @return ResponseInterface|Page
     */
    private function newPattern()
    {
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('New Pattern'));
        return $resultPage;
    }

    /**
     * @param int|string $id
     * @return ResponseInterface|ResultInterface|Page
     */
    private function editPattern($id)
    {
        try {
            $model = $this->repository->getById($id);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect("/*/*/grid");
        }
        $formData = $this->_getSession()->getFormData(true);
        if (!empty($formData)) {
            $model->setData($formData);
        }
        $model->setId($id); // As this field overrided by setData
        $this->registry->register("pattern_model", $model);
        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Pattern: %1 (%2)', $model->getName(), $id));
        return $resultPage;
    }

    /**
     * @return Page|ResponseInterface
     */
    private function createPage()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Patterns'), __('Patterns'));
        $resultPage->getConfig()->getTitle()->prepend(__('Edit Pattern'));
        return $resultPage;
    }
}
