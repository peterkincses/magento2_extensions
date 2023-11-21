<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\RestrictedWord;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class AddButton implements ButtonProviderInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

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

    /**
     * @return mixed[]
     */
    public function getButtonData(): array
    {
        if ($this->authorization->isAllowed('BAT_DevicePersonalisation::restricted_word_create')) {
            return [
                'label' => __('Add Word'),
                'on_click' => sprintf("location.href = '%s';", $this->getUrl('*/*/new')),
                'class' => 'primary',
                'sort_order' => 10,
            ];
        }

        return [];
    }

    /**
     * @param mixed[] $params
     */
    private function getUrl(string $route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
