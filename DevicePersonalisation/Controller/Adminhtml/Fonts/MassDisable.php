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

class MassDisable extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::font_edit';

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

    public function execute(): ResultInterface
    {
        $store = (int) $this->getRequest()->getParam('store');
        $resultRedirect = $this->resultRedirectFactory->create();
        $params = [];
        if ($store) {
            $params['store'] = $store;
        }
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collectionSize = $collection->getSize();
            foreach ($collection as $item) {
                $font = $this->repository->getById($item->getFontId());
                if ($store) {
                    $storeData = $font->getStoreData($store);
                    if (!$storeData) {
                        $storeData = [
                            'font_id' => $font->getId(),
                            'store_id' => $store,
                            'name' => null,
                            'preview_text' => null,
                            'font_size' => null,
                        ];
                    }
                    $storeData['status'] = 0;
                    $this->repository->saveStoreData($font, $storeData);
                } else {
                    $font->setStatus(0);
                    $this->repository->save($font);
                }
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been disabled.', $collectionSize)
            );
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('*/*/grid', $params);
    }

    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed(self::ADMIN_RESOURCE);
    }
}
