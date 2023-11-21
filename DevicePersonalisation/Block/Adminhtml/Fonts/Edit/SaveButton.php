<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Adminhtml\Fonts\Edit;

use BAT\DevicePersonalisation\Api\Data\PsnFontsInterface;
use BAT\DevicePersonalisation\Api\PsnFontsRepositoryInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class SaveButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var PsnFontsRepositoryInterface
     */
    protected $fontsRepository;

    public function __construct(
        Context $context,
        PsnFontsRepositoryInterface $fontsRepository
    ) {
        $this->context = $context;
        $this->fontsRepository = $fontsRepository;
    }

    public function getUrl(string $route = '', array $param = []): string
    {
        $store = $this->context->getRequest()->getParam('store');
        if ($store) {
            $param = array_merge($param, ['store' => $store]);
        }
        return $this->context->getUrlBuilder()->getUrl($route, $param);
    }

    public function getButtonData(): array
    {
        $authorization = $this->context->getAuthorization();
        $isAllowed = $authorization->isAllowed('BAT_DevicePersonalisation::font_edit');
        if (!$isAllowed) {
            return [];
        }
        return [
            'label' => __('Save'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'save']],
                'form-role' => 'save',
            ],
            'sort_order' => 90,
            'url' => $this->getUrl('*/*/save'),
        ];
    }
}
