<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\Patterns;

use Magento\Framework\Controller\ResultInterface;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Grid
 */
class Grid extends Action
{
    public const ADMIN_RESOURCE = 'BAT_DevicePersonalisation::pattern';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->storeManager = $storeManager;
    }

    public function execute(): ResultInterface
    {
        $storeId = (int) $this->getRequest()->getParam('store');
        $store = $this->storeManager->getStore($storeId);
        $this->storeManager->setCurrentStore($store->getCode());

        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Patterns'), __('Patterns'));
        $resultPage->getConfig()->getTitle()->prepend(__('Patterns'));

        return $resultPage;
    }
}
