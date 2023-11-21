<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\Listing\Columns\Report;

use BAT\DevicePersonalisation\Helper\Data;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\AuthorizationInterface;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;

class Actions extends Column
{
    public const URL_PATH_VIEW = 'sales/order/view';

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Data $helper,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
    
    /**
     * {@inheritDoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $actions = [];
                if (isset($item['order_id'])) {
                    $actions['view'] = [
                        'href' => $this->urlBuilder->getUrl(
                            static::URL_PATH_VIEW,
                            ['order_id' => $item['order_id']]
                        ),
                        'label' => __('view'),
                    ];
                    $item[$this->getData('name')] = $actions;
                }
            }
        }
        return $dataSource;
    }
}
