<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Adminhtml\RestrictedWords\Edit;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    public function __construct(
        Context $context,
        AuthorizationInterface $authorization
    ) {
        $this->authorization = $authorization;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * @return mixed[]
     */
    public function getButtonData(): array
    {
        if ($this->getWordId() && $this->authorization->isAllowed('BAT_DevicePersonalisation::restricted_word_delete')) {
            // @todo consider changing to use data_attribute like the customer module
            return [
                'label' => __('Delete Word'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\''
                    . __('Are you sure you want to do this?') . '\', \'' . $this->getDeleteUrl()
                    . '\', {"data": {}})',
                'sort_order' => 20,
            ];
        }

        return [];
    }

    private function getWordId(): ?int
    {
        $wordId = $this->context->getRequest()->getParam('word_id');
        return is_numeric($wordId) ? (int) $wordId : $wordId;
    }

    private function getDeleteUrl(): string
    {
        return $this->getUrl(
            '*/*/delete',
            [
                'word_id' => $this->getWordId(),
            ]
        );
    }
}
