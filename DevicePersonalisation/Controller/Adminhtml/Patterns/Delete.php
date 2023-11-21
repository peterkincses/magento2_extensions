<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Patterns;

use BAT\DevicePersonalisation\Api\PsnPatternsRepositoryInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Exception;

/**
 * Class Delete
 */
class Delete extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::pattern_delete';

    /**
     * @var PsnPatternsRepositoryInterface
     */
    protected $patternsRepository;

    public function __construct(
        PsnPatternsRepositoryInterface $patternsRepository,
        Context $context
    ) {
        $this->patternsRepository = $patternsRepository;
        parent::__construct($context);
    }

    public function execute(): void
    {
        $store = $this->getRequest()->getParam('store');
        $params = [];
        if ($store) {
            $params['store'] = $store;
        }
        if ($id = $this->getRequest()->getParam('pattern_id')) {
            try {
                $model = $this->patternsRepository->getById($id);
                if ($store) {
                    $this->patternsRepository->removeStoreData($id, $store);
                    $this->messageManager->addSuccessMessage(__('Pattern was successfully deleted from the given store'));
                } else {
                    $this->patternsRepository->delete($model);
                    $this->messageManager->addSuccessMessage(__('Pattern was successfully deleted'));
                }
                $this->_redirect('*/*/grid', $params);
            } catch (Exception $e) {
                $params['pattern_id'] = $id;
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', $params);
            }
        }
        $this->_redirect('*/*/grid', $params);
    }
}
