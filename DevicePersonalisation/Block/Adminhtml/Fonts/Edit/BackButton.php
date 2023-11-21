<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Adminhtml\Fonts\Edit;

use Magento\Backend\App\Action;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    public function __construct(
        Context $context
    ) {
        $this->context = $context;
    }

    public function getButtonData(): array
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf('location.href = "%s";', $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10,
        ];
    }

    public function getBackUrl(): string
    {
        $store = $this->context->getRequest()->getParam('store');
        if ($store) {
            return $this->context->getUrlBuilder()->getUrl('*/*/grid', ['store' => $store]);
        }
        return $this->context->getUrlBuilder()->getUrl('*/*/grid');
    }
}
