<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Adminhtml\RestrictedWords\Edit;

use Magento\Backend\Block\Widget\Context;

class GenericButton
{
    /**
     * @var Context
     */
    private $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * @param mixed[] $params
     */
    protected function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
