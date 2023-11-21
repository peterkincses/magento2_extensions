<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\RestrictedWords;

use BAT\DevicePersonalisation\Api\PsnRestrictedWordsRepositoryInterface;
use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultInterface;

class Delete extends Action
{
    /**
     * @var PsnRestrictedWordsRepositoryInterface
     */
    public $psnRestrictedWordsRepository;

    public function __construct(
        Action\Context $context,
        PsnRestrictedWordsRepositoryInterface $psnRestrictedWordsRepository
    ) {
        $this->psnRestrictedWordsRepository = $psnRestrictedWordsRepository;
        parent::__construct($context);
    }

    /**
     * phpcs:disable PSR2.Methods.MethodDeclaration
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('BAT_DevicePersonalisation::restricted_word_delete');
    }
    // phpcs:enable

    public function execute(): ResultInterface
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = $this->getRequest()->getParam('word_id', null);
        try {
            $result = $this->psnRestrictedWordsRepository->deleteById((int) $id);
            if ($result) {
                $this->messageManager->addSuccessMessage(__('The restricted word has been deleted'));
            } else {
                $this->messageManager->addErrorMessage(__('The restricted word does not exist'));
            }
        } catch (Exception $exception) {
            $this->messageManager->addErrorMessage($exception->getMessage());
        }
        return $resultRedirect->setPath('*/*/');
    }
}
