<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\Listing\Columns\Fonts;

use BAT\DevicePersonalisation\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class FontsActions extends Column
{
    public const URL_PATH_EDIT     = 'bat_personalisation/fonts/edit';
    public const URL_PATH_DELETE   = 'bat_personalisation/fonts/delete';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var AuthorizationInterface|mixed|null
     */
    protected $authorization;

    public function __construct(
        UrlInterface $urlBuilder,
        Data $helper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        AuthorizationInterface $authorization = null,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->authorization = $authorization ?: ObjectManager::getInstance()->get(
            AuthorizationInterface::class
        );
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if (! isset($item['font_id'])) {
                continue;
            }
            $params = ['font_id' => $item['font_id']];
            $storeId = $this->helper->getRequestStore();
            if ($storeId) {
                $params['store'] = $storeId;
            }
            $actions = [];
            if ($this->authorization->isAllowed('BAT_DevicePersonalisation::font_edit')) {
                $actions['edit'] = [
                    'href' => $this->urlBuilder->getUrl(
                        static::URL_PATH_EDIT,
                        $params
                    ),
                    'label' => __('Edit'),
                    'aclResource' => 'BAT_DevicePersonalisation::font_edit',
                ];
            }
            if ($this->authorization->isAllowed('BAT_DevicePersonalisation::font_delete')) {
                $actions['delete'] = [
                    'href' => $this->urlBuilder->getUrl(
                        static::URL_PATH_DELETE,
                        $params
                    ),
                    'label' => __('Delete'),
                    'confirm' => [
                        'title' => __('Delete "${ $.$data.name }"'),
                        'message' => __('Are you sure you want to delete the font "${ $.$data.name }" ?'),
                    ],
                    'aclResource' => 'BAT_DevicePersonalisation::font_delete',
                ];
            }
            $item[$this->getData('name')] = $actions;
        }

        return $dataSource;
    }
}
