<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Fonts;

use BAT\DevicePersonalisation\Api\PsnFontsRepositoryInterface;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class Delete extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::font_delete';

    /**
     * @var PsnFontsRepositoryInterface
     */
    protected $fontsRepository;

    public function __construct(
        PsnFontsRepositoryInterface $fontsRepository,
        Context $context
    ) {
        $this->fontsRepository = $fontsRepository;
        parent::__construct($context);
    }

    public function execute(): void
    {
        $store = (int) $this->getRequest()->getParam('store');
        $params = [];
        if ($store) {
            $params['store'] = $store;
        }
        if ($id = (int) $this->getRequest()->getParam('font_id')) {
            try {
                $model = $this->fontsRepository->getById($id);
                if ($store) {
                    $this->fontsRepository->removeStoreData($id, $store);
                    $this->messageManager->addSuccessMessage(__('Font was successfully deleted from the given store'));
                } else {
                    $this->fontsRepository->delete($model);
                    $this->messageManager->addSuccessMessage(__('Font was successfully deleted'));
                }
                $this->_redirect('*/*/grid', $params);
            } catch (Exception $e) {
                $params['font_id'] = $id;
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->_redirect('*/*/edit', $params);
            }
        }
        $this->_redirect('*/*/grid', $params);
    }
}
