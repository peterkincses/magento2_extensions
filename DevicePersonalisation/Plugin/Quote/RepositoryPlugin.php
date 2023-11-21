<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\Quote;

use BAT\DevicePersonalisation\Helper\Data as PersonalisationHelper;
use BAT\DevicePersonalisation\Model\PsnItemRepository;
use BAT\DevicePersonalisation\Model\Service\PsnItem as PsnItemService;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\Data\CartInterface;
use Magento\Quote\Model\Quote\Item;
use Psr\Log\LoggerInterface;
use Exception;

class RepositoryPlugin
{
    /**
     * @var PsnItemService
     */
    protected $psnItemService;

    /**
     * @var PersonalisationHelper
     */
    private $dataHelper;

    /**
     * @var PsnItemRepository
     */
    private $psnItemRepository;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        PsnItemService $psnItemService,
        PersonalisationHelper $dataHelper,
        PsnItemRepository $psnItemRepository,
        LoggerInterface $logger
    ) {
        $this->psnItemService = $psnItemService;
        $this->dataHelper = $dataHelper;
        $this->psnItemRepository = $psnItemRepository;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function afterSave(CartRepositoryInterface $subject, $result, CartInterface $quote)
    {
        if ($this->dataHelper->isEnabled()) {
            try {
                foreach ($quote->getAllItems() as $item) {
                    $additionalData = $item->getData('additional_data');
                    if ($additionalData === null) {
                        continue;
                    }
                    if (preg_match("/<<item_id=(\d+)>>/", $additionalData, $matches) !== false) {
                        $id = (int) $matches[1];
                        $psnItem = $this->psnItemService->getItemByQuoteItemId($id);
                        if ($psnItem !== null) {
                            $newId = (int) $item->getId();
                            $psnItem->setQuoteItemId($newId);
                            $this->psnItemRepository->save($psnItem);
                            $additionalData = preg_replace("/<<item_id=\d+>>/", "", $additionalData);
                            if ($additionalData === "") {
                                $additionalData = null;
                            }
                            $item->setAdditionalData($additionalData);
                        }
                    }
                }
            } catch (Exception $e) {
                $this->logger->error('Save after plugin failed because of error: ' . $e->getMessage());
            }
        }
        return $result;
    }
}
