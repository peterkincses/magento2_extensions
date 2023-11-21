<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Fonts;

use BAT\DevicePersonalisation\Api\PsnFontsRepositoryInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnFonts\CollectionFactory;
use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;

class MassDelete extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::font_delete';

    /**
     * @var Filter
     */
    protected $filter;

    /**
     * @var PsnFontsRepositoryInterface
     */
    protected $repository;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        PsnFontsRepositoryInterface $repository,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->repository = $repository;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    public function execute(): void
    {
        $store = (int) $this->getRequest()->getParam('store');
        $params = [];
        if ($store) {
            $params['store'] = $store;
        }
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $item) {
                if ($store) {
                    $this->repository->removeStoreData($item->getFontId(), $store);
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

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
