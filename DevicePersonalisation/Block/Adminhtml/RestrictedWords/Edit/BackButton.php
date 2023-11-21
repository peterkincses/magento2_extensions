<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Adminhtml\RestrictedWords\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class BackButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * @return mixed[]
     */
    public function getButtonData(): array
    {
        return [
            'label' => __('Back'),
            'on_click' => sprintf("location.href = '%s';", $this->getBackUrl()),
            'class' => 'back',
            'sort_order' => 10,
        ];
    }

    private function getBackUrl(): string
    {
        return $this->getUrl('*/*/');
    }
}
