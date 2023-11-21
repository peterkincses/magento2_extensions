<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Patterns;

use BAT\DevicePersonalisation\Api\PsnPatternsRepositoryInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnPatterns\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Exception;

/**
 * Class MassDelete
 */
class MassDelete extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::pattern_delete';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var PsnPatternsRepositoryInterface
     */
    protected $repository;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        PsnPatternsRepositoryInterface $repository,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     */
    public function execute(): void
    {
        $store = $this->getRequest()->getParam('store');
        $params = [];
        if ($store) {
            $params['store'] = $store;
        }
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $item) {
                if ($store) {
                    $this->repository->removeStoreData($item->getId(), $store);
                } else {
                    $this->repository->delete($item);
                }
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $collectionSize)
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->_redirect('*/*/grid', $params);
    }
}
