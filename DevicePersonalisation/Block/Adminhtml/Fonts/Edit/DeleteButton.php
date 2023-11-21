<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Adminhtml\Fonts\Edit;

use BAT\DevicePersonalisation\Api\Data\PsnFontsInterface;
use BAT\DevicePersonalisation\Api\PsnFontsRepositoryInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

class DeleteButton implements ButtonProviderInterface
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

    public function getFontId(): ?int
    {
        try {
            return (int) $this->fontsRepository->getById((int) $this->context->getRequest()->getParam('font_id'))->getFontId();
        } catch (NoSuchEntityException $e) {
            return null;
        }
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
        $isAllowed = $authorization->isAllowed('BAT_DevicePersonalisation::font_delete');
        if (!$isAllowed) {
            return [];
        }
        return [
            'label' => __('Delete'),
            'class' => 'delete',
            'data_attribute' => [
                'mage-init' => ['button' => ['event' => 'delete']],
                'form-role' => 'delete',
            ],
            'on_click'   => 'deleteConfirm(\'' . __('Are you sure you want to delete font?') . '\', \'' . $this->getDeleteUrl() . '\')',
            'sort_order' => 90,
        ];
    }

    public function getDeleteUrl(): string
    {
        return $this->getUrl('*/*/delete', ['font_id' => $this->getFontId()]);
    }
}
