<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Adminhtml\Icon\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        $data = [];
        if (!$this->authorization->isAllowed('BAT_DevicePersonalisation::icon_delete')) {
            return [];
        }

        if ($this->getIconId()) {
            $overriddenRecord = $this->psnIconRepository->getIconsByStoreId($this->getStoreId(), $this->getIconId());
            if (!empty($this->getStoreId()) && empty($overriddenRecord)) {
                return [];
            }
            $data = [
                'label' => __('Delete Icon'),
                'class' => 'delete',
                'on_click' => 'deleteConfirm(\'' . __(
                    'Are you sure you want to do this?'
                ) . '\', \'' . $this->getDeleteUrl() . '\', {"data": {}})',
                'sort_order' => 20,
            ];
        }
        return $data;
    }

    public function getDeleteUrl(): string
    {
        return $this->getUrl('*/*/delete', [
            'icon_id' => $this->getIconId(), 'store' => $this->getStoreId(),
        ]);
    }
}
