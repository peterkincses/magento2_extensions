<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Adminhtml\Patterns\Edit;

use BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface;
use BAT\DevicePersonalisation\Api\PsnPatternsRepositoryInterface;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;

class DeleteButton implements ButtonProviderInterface
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

    public function getPatternId(): ?int
    {
        try {
            return (int) $this->patternsRepository->getById($this->context->getRequest()->getParam("pattern_id"))->getId();
        } catch (NoSuchEntityException $e) {
            return null;
        }
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
        $isAllowed = $authorization->isAllowed('BAT_DevicePersonalisation::pattern_delete');
        if (!$this->getPatternId() || !$isAllowed) {
            return [];
        }
        return [
            'label' => __('Delete'),
            'class' => 'delete',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'delete']],
                'form-role' => 'delete',
            ],
            'on_click'   => 'deleteConfirm(\'' . __('Are you sure you want to delete pattern?') . '\', \'' . $this->getDeleteUrl() . '\')',
            'sort_order' => 90,
        ];
    }

    public function getDeleteUrl(): string
    {
        return $this->getUrl('*/*/delete', ['pattern_id' => $this->getPatternId()]);
    }
}
