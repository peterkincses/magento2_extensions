<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\MatrixRates;

use Exception;
use Psr\Log\LoggerInterface;
use BAT\DevicePersonalisation\Model\Service\PsnItem;
use Magento\Quote\Model\Quote\Address\RateRequest;
use BAT\DevicePersonalisation\Helper\Data;
use Mageside\ShippingMatrixRates\Model\Carrier\Matrixrates;

class MatrixRatesPlugin
{
    /**
     * @var PsnItem
     */
    protected $psnItem;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        PsnItem $psnItem,
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->psnItem = $psnItem;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param array|mixed $result
     * @return array|mixed
     */
    public function afterGetRate(Matrixrates $subject, $result, RateRequest $request)
    {
        try {
            $hasPersonalisation = $this->hasPersonalisedItems($request);
            if ($hasPersonalisation) {
                return array_filter(
                    $result,
                    function ($rate) {
                        return (
                            isset($rate['exclude_personalisation']) && !$rate['exclude_personalisation']
                        );
                    }
                );
            }
        } catch (Exception $e) {
            $this->logger->error('After getRates plugin failed with message: ' . $e->getMessage());
        }
        return $result;
    }

    private function hasPersonalisedItems(RateRequest $request): bool
    {
        foreach ($request->getAllItems() as $item) {
            $itemId = (int) $item->getId();
            $psnItem = $this->psnItem->getItemByQuoteItemId($itemId);
            if (!empty($psnItem)) {
                return true;
            }
        }
        return false;
    }
}
