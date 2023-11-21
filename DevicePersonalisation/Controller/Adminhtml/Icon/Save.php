<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Icon;

use BAT\DevicePersonalisation\Api\PsnIconRepositoryInterface;
use BAT\DevicePersonalisation\Model\PsnIcon;
use BAT\DevicePersonalisation\Model\PsnIconFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;

class Save extends Action
{
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var PsnIconFactory
     */
    private $psnIconFactory;

    /**
     * @var PsnIconRepositoryInterface
     */
    private $psnIconRepository;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DataPersistorInterface $dataPersistor,
        PsnIconFactory $psnIconFactory,
        PsnIconRepositoryInterface $psnIconRepository
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->psnIconFactory = $psnIconFactory;
        $this->psnIconRepository = $psnIconRepository;
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getParams();
        $postParams = [];
        if ($data) {
            if (empty($data['icon_id'])) {
                $postParams['icon_id'] = null;
            }
            
            if (isset($data['image'][0]['file'])) {
                $postParams['image'] = $data['image'][0]['file'];
            }
            if (isset($data['thumbnail'][0]['file'])) {
                $postParams['thumbnail'] = $data['thumbnail'][0]['file'];
            }
            if (isset($data['store']) && !empty($data['store'])) {
                $postParams['store_id'] = $data['store'];
                $postParams['overriden_name'] = $data['use_default']['name'] ? null : $data['name'];
                $postParams['overriden_status'] = $data['use_default']['status'] ? null : $data['status'];
            } else {
                $postParams['name'] = $data['name'];
                $postParams['status'] = $data['status'];
            }

            /** @var \BAT\DevicePersonalisation\Model\PsnIcon $model */
            $model = $this->psnIconFactory->create();

            $id = $this->getRequest()->getParam('icon_id');
            
            if ($id) {
                try {
                    $model = $this->psnIconRepository->getById((int) $id);
                    $model->addData($postParams);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('This icon no longer exists.'));
                    return $resultRedirect->setPath('*/*/');
                }
            } else {
                $model->setData($postParams);
            }

            try {
                $this->psnIconRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You have saved the Icon.'));
                $this->dataPersistor->clear('personalisation_icon');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Icon.'));
            }
        }
        if (isset($data['store'])) {
            return $resultRedirect->setPath('*/*/', ['store' => $data['store']]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
