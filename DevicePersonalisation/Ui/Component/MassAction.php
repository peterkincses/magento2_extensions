<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\MassAction as UiMassaction;

class MassAction extends UiMassaction
{

    /**
     * Constructor
     *
     * @param mixed[] $components
     * @param mixed[] $data
     */
    public function __construct(
        AuthorizationInterface $authorization,
        ContextInterface $context,
        array $components = [],
        array $data = []
    ) {
        $this->authorization = $authorization;
        parent::__construct($context, $components, $data);
    }

    public function prepare(): void
    {
        parent::prepare();
        $notAllowedActions = [];
        if (!$this->authorization->isAllowed('BAT_DevicePersonalisation::icon_delete')) {
            $notAllowedActions[] = 'delete';
        }
        if (!$this->authorization->isAllowed('BAT_DevicePersonalisation::icon_edit')) {
            $notAllowedActions[] = 'disable';
            $notAllowedActions[] = 'enable';
        }
        $config = $this->getConfiguration();
        $allowedActions = [];
        foreach ($config['actions'] as $action) {
            if (!in_array($action['type'], $notAllowedActions)) {
                $allowedActions[] = $action;
            }
        }
        $config['actions'] = $allowedActions;
        if (!empty($allowedActions)) {
            $this->setData('config', (array) $config);
        } else {
            $this->setData('config', '');
        }
    }
}
