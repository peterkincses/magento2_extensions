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
class MassEnable extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::pattern_edit';

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
    public function execute()
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
                $pattern = $this->repository->getById($item->getId());
                if ($store) {
                    $storeData = $pattern->getStoreData($store);
                    if (!$storeData) {
                        $storeData = [
                            "pattern_id" => $pattern->getId(),
                            "store_id" => $store,
                            "name" => null,
                            "category_name" => null,
                        ];
                    }
                    $storeData['status'] = 1;
                    $this->repository->saveStoreData($pattern, $storeData);
                } else {
                    $pattern->setStatus(1);
                    $this->repository->save($pattern);
                }
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been enabled.', $collectionSize)
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        $this->_redirect('*/*/grid', $params);
    }
}
