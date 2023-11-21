<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Block\Adminhtml\Icon\Edit;

use BAT\DevicePersonalisation\Api\PsnIconRepositoryInterface;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class GenericButton
{
    /**
     * @var Context
     */
    protected $context;

    /**
     * @var PsnIconRepositoryInterface
     */
    protected $psnIconRepository;

    /**
     * @var AuthorizationInterface
     */
    protected $authorization;

    public function __construct(
        Context $context,
        PsnIconRepositoryInterface $psnIconRepository,
        AuthorizationInterface $authorization
    ) {
        $this->context = $context;
        $this->authorization = $authorization;
        $this->psnIconRepository = $psnIconRepository;
    }

    public function getIconId(): ?int
    {
        try {
            return $this->psnIconRepository->getById(
                (int) $this->context->getRequest()->getParam('icon_id')
            )->getIconId();
        } catch (NoSuchEntityException $e) {
            return null;
        }
    }

    public function getStoreId(): ?int
    {
        return (int) $this->context->getRequest()->getParam('store');
    }

    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->context->getUrlBuilder()->getUrl($route, $params);
    }
}
