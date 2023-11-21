<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Adminhtml\Patterns\Edit;

use BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface;
use BAT\DevicePersonalisation\Api\PsnPatternsRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class SaveButton implements ButtonProviderInterface
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var PsnPatternsRepositoryInterface
     */
    protected $patternsRepository;

    public function __construct(
        Context $context,
        PsnPatternsRepositoryInterface $patternsRepository
    ) {
        $this->context = $context;
        $this->patternsRepository = $patternsRepository;
    }

    public function getUrl(string $route = '', array $param = []): string
    {
        $store = $this->context->getRequest()->getParam("store");
        if ($store) {
            $param = array_merge($param, ["store" => $store]);
        }
        return $this->context->getUrlBuilder()->getUrl($route, $param);
    }

    public function getButtonData(): array
    {
        $authorization = $this->context->getAuthorization();
        $isAllowed = $authorization->isAllowed('BAT_DevicePersonalisation::pattern_edit');
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
            'url' => $this->getUrl("*/*/save"),
        ];
    }
}
