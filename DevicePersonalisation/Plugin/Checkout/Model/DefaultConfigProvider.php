<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\Checkout\Model;

use BAT\DevicePersonalisation\Helper\Data as PersonalisationHelper;
use BAT\DevicePersonalisation\Model\Service\PsnItem as PsnItemService;
use Magento\Checkout\Model\DefaultConfigProvider as DefaultConfigProviderCore;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Model\AbstractModel;
use Magento\Quote\Api\CartItemRepositoryInterface as QuoteItemRepository;

class DefaultConfigProvider extends AbstractModel
{
    /**
     * @var CheckoutSession
     */
    protected $checkoutSession;

    /**
     * @var QuoteItemRepository
     */
    protected $quoteItemRepository;

    /**
     * @var PsnItemService
     */
    protected $psnItemService;

    /**
     * @var PersonalisationHelper
     */
    private $dataHelper;

    public function __construct(
        CheckoutSession $checkoutSession,
        QuoteItemRepository $quoteItemRepository,
        PsnItemService $psnItemService,
        PersonalisationHelper $dataHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quoteItemRepository = $quoteItemRepository;
        $this->psnItemService = $psnItemService;
        $this->dataHelper = $dataHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function afterGetConfig(DefaultConfigProviderCore $subject, array $result)
    {
        if (!$this->dataHelper->isEnabled()) {
            return $result;
        } else {
            $result['isDevicePersonalisationEnabled'] = true;
        }

        $psnItems = [];
        $quoteId = $this->checkoutSession->getQuote()->getId();
        if ($quoteId) {
            $items = $result['quoteItemData'];
            $itemOptionCount = count($items);
            $quoteItems = $this->quoteItemRepository->getList($quoteId);

            foreach ($quoteItems as $quoteItem) {
                $itemId = (int) $quoteItem->getItemId();
                if ($quoteItem->getHasChildren()) {
                    foreach ($quoteItem->getChildren() as $child) {
                        $itemId = (int) $child->getId();
                        break;
                    }
                }

                $psnItemModel = $this->psnItemService->getItemByQuoteItemId($itemId);
                if (!is_null($psnItemModel)) {
                    $psnData = $psnItemModel->getData();
                    foreach ($psnData as $key => $value) {
                        if (strpos($key, "orientation") !== false) {
                            $psnData[$key] = __($value);
                        }
                    }
                    $psnItems[$quoteItem->getItemId()] = $psnData;
                }
            }

            for ($i = 0; $i < $itemOptionCount; $i++) {
                $itemId = $items[$i]['item_id'];
                if (array_key_exists($itemId, $psnItems)) {
                    $items[$i]['psn_data'] = $psnItems[$itemId];
                }
            }
            $result['quoteItemData'] = $items;
        }
        return $result;
    }
}
