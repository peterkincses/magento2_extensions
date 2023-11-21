<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Icon;

use BAT\DevicePersonalisation\Model\ResourceModel\PsnIcon\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action
{
    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute(): ResultInterface
    {
        $storeId = $this->getRequest()->getParam('store');
        $collection = $this->filter->getCollection($this->collectionFactory->create());
        $collectionSize = $collection->getSize();
        if (!empty($storeId)) {
            foreach ($collection as $item) {
                $item->setStoreId($storeId)->setIsDelete(1)->save();
            }
            $this->messageManager->addNotice(__('Main table records will not be deleted from store view scope.'));
        } else {
            foreach ($collection as $item) {
                $item->delete();
            }
        }
        $this->messageManager->addSuccess(__('A total of %1 icon(s) have been deleted.', $collectionSize));

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/', ['store' => $storeId]);
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('BAT_DevicePersonalisation::icon_delete');
    }
}
