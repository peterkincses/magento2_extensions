<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\Controller;

use BAT\DevicePersonalisation\Helper\Data;
use Exception;
use Magento\Checkout\Model\Cart;
use Magento\Framework\App\Action\Context;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Sales\Controller\AbstractController\OrderLoaderInterface;
use Magento\Sales\Controller\Order\Reorder;
use Magento\Sales\Model\Order\Item;

class ReorderPlugin
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var OrderLoaderInterface
     */
    private $orderLoader;

    /**
     * @var Registry
     */
    private $coreRegistry;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RedirectFactory
     */
    private $resultRedirectFactory;

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Session
     */
    private $session;

    public function __construct(
        Context $context,
        OrderLoaderInterface $orderLoader,
        Registry $registry,
        Data $helper,
        Cart $cart,
        Session $session
    ) {
        $this->context = $context;
        $this->orderLoader = $orderLoader;
        $this->coreRegistry = $registry;
        $this->messageManager = $context->getMessageManager();
        $this->request = $context->getRequest();
        $this->resultRedirectFactory = $context->getResultRedirectFactory();
        $this->helper = $helper;
        $this->cart = $cart;
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function aroundExecute(Reorder $subject, callable $proceed)
    {
        if ($this->helper->isEnabled()) {
            $result = $this->orderLoader->load($this->request);
            if ($result instanceof ResultInterface) {
                return $result;
            }
            $order = $this->coreRegistry->registry('current_order');
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultRedirectFactory->create();

            $items = $order->getItemsCollection();
            $personalisationItemWasRemoved = false;
            $personalisationReOrderRequired = $this->helper->isPersonalisationReorderRequired();
            $messageAfterRemovingPersonalisation = __($this->helper->getMessageAfterRemovingPersonalisation());
            foreach ($items as $item) {
                try {
                    if ($item->getProduct()->getPsnIsPersonalisable() && $personalisationReOrderRequired) {
                        if (!$personalisationItemWasRemoved) {
                            $personalisationItemWasRemoved = true;
                            $this->messageManager->addErrorMessage($messageAfterRemovingPersonalisation);
                        }
                    } else {
                        $this->cart->addOrderItem($item);
                    }
                } catch (LocalizedException $e) {
                    if ($this->session->getUseNotice(true)) {
                        $this->messageManager->addNoticeMessage($e->getMessage());
                    } else {
                        $this->messageManager->addErrorMessage($e->getMessage());
                    }
                    return $resultRedirect->setPath('*/*/history');
                } catch (Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('We can\'t add this item to your shopping cart right now.')
                    );
                    return $resultRedirect->setPath('checkout/cart');
                }
            }

            $this->cart->save();
            return $resultRedirect->setPath('checkout/cart');
        }

        return $proceed();
    }
}
