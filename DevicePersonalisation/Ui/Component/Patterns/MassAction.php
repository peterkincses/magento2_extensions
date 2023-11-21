<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\Patterns;

use BAT\DevicePersonalisation\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\MassAction as MassActionBase;

/**
 * Class MassAction
 */
class MassAction extends MassActionBase
{
    /**
     * @var AuthorizationInterface|mixed|null
     */
    protected $authorization;

    /**
     * @var Data
     */
    protected $helper;

    public function __construct(
        Data $helper,
        ContextInterface $context,
        AuthorizationInterface $authorization = null,
        array $components = [],
        array $data = []
    ) {
        $this->helper = $helper;
        $this->authorization = $authorization ?: ObjectManager::getInstance()->get(
            AuthorizationInterface::class
        );
        parent::__construct($context, $components, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function prepare()
    {
        parent::prepare();
        $store = $this->context->getRequestParam("store");
        $notAllowedActions = [];
        if (!$this->authorization->isAllowed('BAT_DevicePersonalisation::pattern_delete')) {
            $notAllowedActions[] = 'delete';
        }
        if (!$this->authorization->isAllowed('BAT_DevicePersonalisation::pattern_edit')) {
            $notAllowedActions[] = 'disable';
            $notAllowedActions[] = 'enable';
        }

        $config = $this->getConfiguration();
        $allowedActions = [];
        foreach ($config['actions'] as $action) {
            if ($store) {
                $action['url'] = $action['url'] . 'store/' . $store . '/';
            }
            if (!in_array($action['type'], $notAllowedActions)) {
                $allowedActions[] = $action;
            }
        }
        $config['actions'] = $allowedActions;
        $this->setData('config', (array) $config);
    }
}
