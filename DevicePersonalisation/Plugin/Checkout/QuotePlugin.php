<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\Checkout;

use BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface;
use BAT\DevicePersonalisation\Helper\Data as PersonalisationHelper;
use BAT\DevicePersonalisation\Model\Config\PersonalisationPrices;
use BAT\DevicePersonalisation\Model\Service\PsnItem as PsnItemService;
use BAT\DevicePersonalisation\Model\TaxCalculation;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DataObject;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;
use Magento\Quote\Model\Quote\Item\AbstractItem;
use Magento\Quote\Model\ResourceModel\Quote as QuoteResource;

class QuotePlugin
{
    public const ITEM_TABLE = 'psn_item';

    /**
     * @var PsnItemService
     */
    protected $psnItemService;
    /**
     * @var QuoteResource
     */
    protected $resource;

    /**
     * @var PersonalisationPrices
     */
    protected $personalisationPrices;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var TaxCalculation
     */
    protected $taxCalculation;

    /**
     * @var PersonalisationHelper
     */
    private $dataHelper;

    public function __construct(
        PsnItemService $psnItemService,
        QuoteResource $resource,
        PersonalisationPrices $personalisationPrices,
        PersonalisationHelper $dataHelper,
        RequestInterface $request,
        TaxCalculation $taxCalculation
    ) {
        $this->psnItemService = $psnItemService;
        $this->resource = $resource;
        $this->personalisationPrices = $personalisationPrices;
        $this->dataHelper = $dataHelper;
        $this->request = $request;
        $this->taxCalculation = $taxCalculation;
    }

    /**
     * {@inheritDoc}
     * @param $result Item|string
     */
    public function afterAddProduct(
        Quote $subject,
        $result,
        $product,
        $request
    ) {
        if (is_string($result) || (!$this->dataHelper->isEnabled())) {
            return $result;
        }
        $params = $this->psnItemService->getFilteredParams($this->request);
        if (!$params && $request instanceof DataObject) {
            $params = $request->getData('psn_data');
        }
        if (!$params) {
            return $result;
        }
        $psnTax = 0;
        $storeId = $result->getQuote()->getStoreId();
        $psnPrice = $this->personalisationPrices->getFinalPrice($storeId);
        $beforePsnPrices = $this->taxCalculation->getPriceInclAndExclTax(
            (int) $result->getProductId(),
            (float) $result->getPrice()
        );
        if ($beforePsnPrices) {
            $beforePsnExcTax = $beforePsnPrices['incl'] - $beforePsnPrices['excl'];
            $beforePsnExcTax = round($beforePsnExcTax, 2);
            $afterPsnPrices = $this->taxCalculation->getPriceInclAndExclTax(
                (int) $result->getProductId(),
                (float) ($result->getPrice() + $psnPrice)
            );
            $afterPsnExcTax = $afterPsnPrices['incl'] - $afterPsnPrices['excl'];
            $afterPsnExcTax = round($afterPsnExcTax, 2);
            $psnTax = $afterPsnExcTax - $beforePsnExcTax;
            $psnPrice = $psnPrice - $psnTax;
        }
        $params[PsnItemDataInterface::PERSONALISATION_PRICE] = $psnPrice;
        $params[PsnItemDataInterface::PERSONALISATION_TAX] = $psnTax;
        $params[PsnItemDataInterface::PERSONALISATION_IS_FREE] =
            $this->isFreePersonalisation($psnPrice, $storeId) ? 1 : 0;
        $params[PsnItemDataInterface::FRONT_ICON] = $params['icon'] ?? null;
        $params[PsnItemDataInterface::FRONT_PATTERN] = $params['pattern'] ?? null;

        $isGrouped = $this->request->getParam('super_group');

        if ($isGrouped) {
            $simpleItem = $this->getItemForGroupedProduct($subject);
            if (!is_null($simpleItem)) {
                $this->setItemData($simpleItem, $params);
            }
        } else {
            $this->setItemData($result, $params);
        }

        return $result;
    }

    /**
     * @param  int|string $storeId
     */
    private function isFreePersonalisation(float $psnPrice, $storeId): bool
    {
        return !$psnPrice
            && $this->dataHelper->isFreeEngravingEnabled()
            && $this->personalisationPrices->getPrice($storeId);
    }

    private function setItemData(Item $quoteItem, array $data): void
    {
        if (
            $quoteItem->getHasChildren()
            && $quoteItem->getProductType() == 'configurable'
            && $quoteItem->getChildren()
        ) {
            $child = $quoteItem->getChildren()[0];
            $child->setData('psn_data', $data);
        } else {
            $quoteItem->setData('psn_data', $data);
        }
    }

    private function getItemForGroupedProduct(Quote $quote): ?AbstractItem
    {
        $groupedItems = $this->request->getParam('super_group');
        foreach ($quote->getAllItems() as $item) {
            $product = $item->getProduct();
            $productId = $product->getId();
            if (!array_key_exists($productId, $groupedItems)) {
                continue;
            }
            $isPsn = $product->getResource()->getAttributeRawValue($productId, 'psn_is_personalisable', $quote->getStoreId());
            if ($isPsn) {
                return $item;
            }
        }
        return null;
    }
}
