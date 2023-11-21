<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Icon;

use BAT\DevicePersonalisation\Model\PsnIconFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;

class Delete extends Action
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
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('icon_id');
        $storeId = $this->getRequest()->getParam('store');
        if ($id) {
            try {
                $model = $this->psnIconFactory->create();
                $model->load($id);
                if (!empty($storeId)) {
                    $model->setStoreId($storeId)->setIsDelete(1)->save();
                } else {
                    $model->delete();
                }
                $this->messageManager->addSuccessMessage(__('You have successfully deleted the icon.'));
                return $resultRedirect->setPath('*/*/', ['store' => $storeId]);
            } catch (Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/edit', ['icon_id' => $id, 'store' => $storeId]);
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find a icon to delete.'));
        return $resultRedirect->setPath('*/*/', ['store' => $storeId]);
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('BAT_DevicePersonalisation::icon_delete');
    }
}
