<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Action;

class DisableAction extends Action
{
    /**
     * @var Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @param mixed[] $components
     * @param mixed[] $data
     * @param mixed[]|\JsonSerializable $actions
     */
    public function __construct(
        ContextInterface $context,
        RequestInterface $request,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = [],
        $actions = null
    ) {
        parent::__construct($context, $components, $data, $actions);
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
    }

    public function prepare(): void
    {
        parent::prepare();
        $config = $this->getConfiguration();
        $params = ['store' => $this->request->getParam('store')];
        $config['url'] = $this->urlBuilder->getUrl($config['urlPath'], $params);
        $this->setData('config', $config);
    }
}
