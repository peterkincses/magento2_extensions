<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\RestrictedWords;

use BAT\DevicePersonalisation\Model\ResourceModel\PsnRestrictedWords\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;

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
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        foreach ($collection as $item) {
            $item->delete();
        }

        $this->messageManager->addSuccess(__('A total of %1 record(s) have been deleted.', $collectionSize));

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/');
    }
}
