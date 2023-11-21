<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\RestrictedWords;

use BAT\DevicePersonalisation\Api\PsnRestrictedWordsRepositoryInterface;
use BAT\DevicePersonalisation\Model\PsnRestrictedWordsFactory;
use Exception as Exception;
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
    private $dataPersistor;

    /**
     * @var PsnRestrictedWordsFactory
     */
    private $psnRestrictedWordsFactory;

    /**
     * @var PsnRestrictedWordsRepositoryInterface
     */
    private $psnRestrictedWordsRepository;

    public function __construct(
        Context $context,
        Registry $coreRegistry,
        DataPersistorInterface $dataPersistor,
        PsnRestrictedWordsFactory $psnRestrictedWordsFactory,
        PsnRestrictedWordsRepositoryInterface $psnRestrictedWordsRepository
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->psnRestrictedWordsFactory = $psnRestrictedWordsFactory;
        $this->psnRestrictedWordsRepository = $psnRestrictedWordsRepository;

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
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        $postParams = [];
        if ($data) {
            if (empty($data['word_id'])) {
                $postParams['word_id'] = null;
            }
            $postParams['restricted_word'] = trim($data['restricted_word']);
            $postParams['store_id'] = $data['store_id'];

            /** @var \BAT\DevicePersonalisation\Model\PsnRestrictedWords $model */
            $model = $this->psnRestrictedWordsFactory->create();
            $id = $this->getRequest()->getParam('word_id');
            if ($id) {
                try {
                    $model = $this->psnRestrictedWordsRepository->getById((int) $id);
                    // @todo this breaks the interface
                    $model->addData($postParams);
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage(__('The restricted word no longer exists'));
                    return $resultRedirect->setPath('*/*/');
                }
            } else {
                $model->setData($postParams);
            }

            try {
                $this->psnRestrictedWordsRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the restricted word'));
                $this->dataPersistor->clear('personalisation_word');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage(
                    $e,
                    __('Something went wrong while saving the restricted word')
                );
            }
        }
        return $resultRedirect->setPath('*/*/');
    }
}
