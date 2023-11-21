<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Adminhtml\Icon\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Ui\Component\Control\Container;

class SaveButton extends GenericButton implements ButtonProviderInterface
{
    public function getButtonData(): array
    {
        if (!$this->authorization->isAllowed('BAT_DevicePersonalisation::icon_edit')) {
            return [];
        }

        return
            [
                'label' => __('Save'),
                'class' => 'save primary',
                'on_click' => '',
                'sort_order' => 80,
                'data_attribute' => [
                    'mage-init' => [
                        'Magento_Ui/js/form/button-adapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'psn_icon_form.psn_icon_form',
                                    'actionName' => 'save',
                                    'params' => [
                                        true,
                                        ['store' => $this->getStoreId()],
                                    ],
                                ],
                            ],
                        ],
                    ],

                ],
            ];
    }
}
