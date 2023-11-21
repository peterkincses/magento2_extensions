<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Fonts;

use BAT\DevicePersonalisation\Api\Data\PsnFontsInterface;
use BAT\DevicePersonalisation\Api\PsnFontsRepositoryInterface;
use BAT\DevicePersonalisation\Model\PsnFonts;
use BAT\DevicePersonalisation\Model\PsnFontsFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Model\AbstractModel;

class Save extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::font';

    /**
     * @var PsnFontsRepositoryInterface
     */
    protected $repository;

    /**
     * @var PsnFontsFactory
     */
    protected $factory;

    public function __construct(
        Context $context,
        PsnFontsRepositoryInterface $repository,
        PsnFontsFactory $factory
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->factory = $factory;
    }

    public function execute(): ResultInterface
    {
        $data = $this->getRequest()->getPostValue();
        $store = (int) $this->getRequest()->getParam('store');

        if (empty($data)) {
            return $this->_redirect('/*/*/grid');
        }

        $id = (int) $this->getRequest()->getParam('font_id');

        // Handle save
        try {
            $model = $this->getModel($id);
            if (!$store) {
                $model->setData($data);
            }
            if (!$model->getId()) {
                $model->unsetData('font_id');
            }
            // save font_file only for default store
            if (!$store && isset($data['font_file'])) {
                $img = $data['font_file'][0];
                $model->setFontFile($img['name']);
            }
            $this->_getSession()->setPageData($model->getData());
            if (!$store) {
                $model = $this->repository->save($model);
            } else {
                $defaultParam = $this->getRequest()->getParam('use_default');
                $storeName = $data['name'];
                if ($defaultParam['name']) {
                    $storeName = null;
                }
                $storeStatus = $data['status'];
                if ($defaultParam['status']) {
                    $storeStatus = null;
                }
                $previewText = $data['preview_text'];
                if ($defaultParam['preview_text']) {
                    $previewText = null;
                }
                $fontSize = $data['font_size'];
                if ($defaultParam['font_size']) {
                    $fontSize = null;
                }

                $this->repository->saveStoreData($model, [
                    'font_id' => $model->getId(),
                    'store_id' => $store,
                    'name' => $storeName,
                    'status' => $storeStatus,
                    'preview_text' => $previewText,
                    'font_size' => $fontSize,
                ]);
            }
            $this->messageManager->addSuccessMessage(__('Font saved successfully'));
            $this->_getSession()->setPageData(false);
        } catch (Exception $e) {
            return $this->resultRedirectFactory->create()->setPath('*/*/grid');
        }

        // Redirect back if requested
        return $this->returnToIndex($store);
    }

    private function getModel(int $id): PsnFontsInterface
    {
        return empty($id) ? $this->factory->create()->setId(0) : $this->repository->getById($id);
    }

    private function returnToIndex(int $storeId = 0): ResultInterface
    {
        if ($storeId) {
            return $this->resultRedirectFactory->create()->setPath('*/*/grid', ['store' => $storeId]);
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/grid');
    }

    protected function _isAllowed(): bool
    {
        return
            $this->_authorization->isAllowed('BAT_DevicePersonalisation::font_create')
            || $this->_authorization->isAllowed('BAT_DevicePersonalisation::font_edit');
    }
}
