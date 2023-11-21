<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\Listing\Column;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class IconActions extends Column
{
    public const PERSONALISATION_URL_PATH_EDIT = 'bat_personalisation/icon/edit';

    public const PERSONALISATION_URL_PATH_DELETE = 'bat_personalisation/icon/delete';

    /**
     * @var UrlBuilder
     */
    protected $actionUrlBuilder;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        AuthorizationInterface $authorization,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        $this->authorization = $authorization;
        $this->context = $context;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                if (isset($item['icon_id'])) {
                    if ($this->authorization->isAllowed('BAT_DevicePersonalisation::icon_edit')) {
                        $item[$name]['edit'] = [
                            'href' => $this->urlBuilder->getUrl(
                                self::PERSONALISATION_URL_PATH_EDIT,
                                ['icon_id' => $item['icon_id'], 'store' => $item['store']]
                            ),
                            'label' => __('Edit'),
                        ];
                    }
                    if ($this->authorization->isAllowed('BAT_DevicePersonalisation::icon_delete')) {
                        $item[$name]['delete'] = [
                            'href' => $this->urlBuilder->getUrl(
                                self::PERSONALISATION_URL_PATH_DELETE,
                                ['icon_id' => $item['icon_id'], 'store' => $item['store']]
                            ),
                            'label' => __('Delete'),
                        ];
                    }
                }
            }
        }
        return $dataSource;
    }

    public function prepare(): void
    {
        parent::prepare();
        $canEdit = $this->authorization->isAllowed('BAT_DevicePersonalisation::icon_edit');
        $canDelete = $this->authorization->isAllowed('BAT_DevicePersonalisation::icon_delete');
        if (!($canEdit || $canDelete)) {
            $this->_data['config']['componentDisabled'] = true;
        }
    }
}
