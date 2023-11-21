<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\Listing\Columns\Report;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Directory\Model\Currency;

class Price extends Column
{
    /**
     * @var PriceCurrencyInterface
     */
    protected $priceFormatter;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        PriceCurrencyInterface $priceFormatter,
        Currency $currency,
        array $components = [],
        array $data = []
    ) {
        $this->priceFormatter = $priceFormatter;
        $this->currency = $currency;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * {@inheritDoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $currencyCode = isset($item['base_currency_code']) ? $item['base_currency_code'] : null;
                $basePurchaseCurrency = $this->currency->load($currencyCode);
                $item[$this->getData('name')] = $basePurchaseCurrency
                    ->format($item[$this->getData('name')], [], false);
            }
        }

        return $dataSource;
    }
}
