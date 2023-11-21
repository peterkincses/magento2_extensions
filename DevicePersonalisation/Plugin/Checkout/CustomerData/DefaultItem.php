<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\Checkout\CustomerData;

use BAT\DevicePersonalisation\Model\Service\PsnItem;
use Magento\Checkout\CustomerData\DefaultItem as MagentoDefaultItem;
use Magento\Quote\Model\Quote\Item;
use BAT\DevicePersonalisation\Helper\Data as PersonalisationHelper;

class DefaultItem
{
    /**
     * @var PsnItem
     */
    protected $psnItemService;

    /**
     * @var PersonalisationHelper
     */
    private $dataHelper;

    public function __construct(
        PsnItem $psnItemService,
        PersonalisationHelper $dataHelper
    ) {
        $this->psnItemService = $psnItemService;
        $this->dataHelper = $dataHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function afterGetItemData(MagentoDefaultItem $subject, $result, Item $item): array
    {
        if (!$this->dataHelper->isEnabled()) {
            return array_merge($result, [
                'psn_item' => false,
            ]);
        }

        $itemId = (int) $item->getData('item_id');
        if ($item->getHasChildren()) {
            foreach ($item->getChildren() as $child) {
                $itemId = (int) $child->getId();
                break;
            }
        }
        if ($itemId) {
            $psnItemModel = $this->psnItemService->getItemByQuoteItemId($itemId);
            if (!is_null($psnItemModel)) {
                $psnConfigs = [
                    'psn_item' => true,
                    'psn_item_data' => [
                        'front_font' => $psnItemModel->getFrontFont(),
                        'front_text' => $psnItemModel->getFrontText(),
                        'front_orientation' => __($psnItemModel->getFrontOrientation()),
                        'front_pattern' => $psnItemModel->getFrontPattern(),
                        'front_icon' => $psnItemModel->getFrontIcon(),
                        'back_text' => $psnItemModel->getBackText(),
                        'back_font' => $psnItemModel->getBackFont(),
                        'back_orientation' => __($psnItemModel->getBackOrientation()),
                        'personalisation_price' => $psnItemModel->getPersonalisationPrice(),
                    ],
                ];
                return array_merge($result, $psnConfigs);
            }
        }

        return array_merge($result, [
            'psn_item' => false,
        ]);
    }
}
