<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\RestrictedWord;

use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\MassAction as UiMassAction;

class MassAction extends UiMassAction
{
    public const NAME = 'massaction';

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
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

        if (!$this->authorization->isAllowed('BAT_DevicePersonalisation::restricted_word_delete')) {
            $this->setData('config', []);
        }
    }

    public function getComponentName(): string
    {
        return static::NAME;
    }
}
