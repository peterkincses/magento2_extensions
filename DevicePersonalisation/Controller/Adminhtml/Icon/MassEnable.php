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

class MassEnable extends Action
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
        $params = $this->getRequest()->getParams();

        $collection  = $this->collectionFactory->create();
        if (isset($params['selected']) && is_array($params['selected'])) {
            $collection->addFieldToFilter('icon_id', ['in' => $params['selected']]);
        } elseif (isset($params['excluded']) && is_array($params['excluded'])) {
            $collection->addFieldToFilter('icon_id', ['nin' => $params['excluded']]);
        }
        $collectionSize = $collection->getSize();

        if (!empty($storeId)) {
            foreach ($collection as $item) {
                $item->setStoreId($storeId)
                ->setStatusVal(1)
                ->setChangedStatus(1)
                ->save();
            }
        } else {
            foreach ($collection as $item) {
                $item->setStatus(1);
                $item->save();
            }
        }

        $this->messageManager->addSuccess(__('A total of %1 icon(s) have been enabled.', $collectionSize));

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('*/*/', ['store' => $storeId]);
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('BAT_DevicePersonalisation::icon_edit');
    }
}
