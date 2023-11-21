<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Patterns;

use BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface;
use BAT\DevicePersonalisation\Api\PsnPatternsRepositoryInterface;
use BAT\DevicePersonalisation\Model\PsnPatterns;
use BAT\DevicePersonalisation\Model\PsnPatternsFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\App\Action;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Controller\Result\Redirect;
use Exception;

/**
 * Class Save
 */
class Save extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::pattern';

    /**
     * @var PsnPatternsRepositoryInterface
     */
    protected $repository;

    /**
     * @var PsnPatternsFactory
     */
    protected $factory;

    public function __construct(
        Context $context,
        PsnPatternsRepositoryInterface $repository,
        PsnPatternsFactory $factory
    ) {
        parent::__construct($context);
        $this->repository = $repository;
        $this->factory = $factory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $store = $this->getRequest()->getParam("store");

        if (empty($data)) {
            return $this->_redirect("/*/*/grid");
        }

        $id = $this->getRequest()->getParam('pattern_id');

        // Handle save
        try {
            $model = $this->getModel($id);
            if (!$store) {
                $model->setData($data);
            }
            if (!$model->getId()) {
                $model->unsetData("pattern_id");
            }
            // save image only for default store
            if (!$store && isset($data["image"])) {
                $img = $data["image"][0];
                $model->setImage($img["name"]);
            }
            if (!$store && isset($data["thumbnail"])) {
                $thumbnail = $data["thumbnail"][0];
                $model->setThumbnail($thumbnail["name"]);
            }
            $this->_getSession()->setPageData($model->getData());
            if (!$store) {
                $model = $this->repository->save($model);
            } else {
                $defaultParam = $this->getRequest()->getParam("use_default");
                $storeName = $data['name'];
                if ($defaultParam['name']) {
                    $storeName = null;
                }
                $storeStatus = $data['status'];
                if ($defaultParam['status']) {
                    $storeStatus = null;
                }
                $this->repository->saveStoreData($model, [
                    'pattern_id' => $model->getId(),
                    'store_id' => $store,
                    'name' => $storeName,
                    'category_name' => $data["category_name"],
                    'status' => $storeStatus,
                ]);
            }
            $this->messageManager->addSuccessMessage(__('Pattern saved successfully'));
            $this->_getSession()->setPageData(false);
        } catch (Exception $e) {
            return $this->resultRedirectFactory->create()->setPath('*/*/grid');
        }

        // Redirect back if requested
        return $this->returnToIndex($store);
    }

    /**
     * @param null $id
     * @return PsnPatternsInterface|PsnPatterns|AbstractModel
     */
    private function getModel($id = null)
    {
        return $id === null || $id === "" ? $this->factory->create()->setId(0) : $this->repository->getById($id);
    }

    /**
     * {@inheritDoc}
     */
    private function returnToIndex($storeId = 0)
    {
        if ($storeId) {
            return $this->resultRedirectFactory->create()->setPath('*/*/grid', ["store" => $storeId]);
        }
        return $this->resultRedirectFactory->create()->setPath('*/*/grid');
    }

    /**
     * {@inheritDoc}
     */
    protected function _isAllowed()
    {
        return
            $this->_authorization->isAllowed('BAT_DevicePersonalisation::pattern_create')
            || $this->_authorization->isAllowed('BAT_DevicePersonalisation::pattern_edit');
    }
}
