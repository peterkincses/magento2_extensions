<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Icons;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class AddButton implements ButtonProviderInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    public function __construct(
        Context $context,
        AuthorizationInterface $authorization
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->authorization = $authorization;
    }

    public function getButtonData(): array
    {
        if (!$this->authorization->isAllowed('BAT_DevicePersonalisation::icon_create')) {
            return [];
        }

        return [
            'label' => __('Add Icon'),
            'on_click' => sprintf("location.href = '%s';", $this->getUrl('*/*/new')),
            'class' => 'primary',
            'sort_order' => 10,
        ];
    }

    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
