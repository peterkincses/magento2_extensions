<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\Service;

use BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface;
use BAT\DevicePersonalisation\Api\PsnItemRepositoryInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnItem as PsnItemResource;
use Magento\Framework\App\RequestInterface;

class PsnItem
{
    /**
     * @var PsnItemResource
     */
    protected $resource;
    /**
     * @var PsnItemRepositoryInterface
     */
    protected $psnItemRepository;

    public function __construct(
        PsnItemResource $resource,
        PsnItemRepositoryInterface $psnItemRepository
    ) {
        $this->resource = $resource;
        $this->psnItemRepository = $psnItemRepository;
    }

    public function getItemByOrderItemId(int $orderItemId): ?PsnItemDataInterface
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTable('psn_item');
        $query = $connection->select()->from(['main_table' => $table], 'entity_id') . ' where order_item_id= ' . (int) $orderItemId;
        $rowData  = $connection->fetchRow($query);
        if (is_array($rowData) && isset($rowData['entity_id'])) {
            $id = (int) $rowData['entity_id'];
        } else {
            return null;
        }
        return $this->psnItemRepository->getById($id);
    }

    public function getItemByQuoteItemId(int $quoteItemId): ?PsnItemDataInterface
    {
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTable('psn_item');
        $query = $connection->select()->from(['main_table' => $table], 'entity_id') . ' where quote_item_id= ' . (int) $quoteItemId;
        $id  = $connection->fetchOne($query);
        if (!$id) {
            return null;
        }
        $id = (int) $id;
        return $this->psnItemRepository->getById($id);
    }

    public function getFilteredParams(RequestInterface $request): ?array
    {
        $isProdPersonalised = (bool) $request->getParam('is_product_personalised');

        if ($isProdPersonalised) {
            $result = [];
            $frontText = $request->getParam('personalisation_front_text');
            if ($frontText) {
                $result['front_text'] = $frontText;

                $frontTextFontFamily = $request->getParam('personalisation_front_text_font_family');
                if ($frontTextFontFamily) {
                    $result['front_font'] = $frontTextFontFamily;
                }

                $frontTextDirection = $request->getParam('personalisation_front_text_direction');
                if ($frontTextDirection) {
                    $result['front_orientation'] = $frontTextDirection;
                }
            }

            $frontPattern = $request->getParam('personalisation_front_pattern');
            if ($frontPattern) {
                $result['front_pattern'] = $frontPattern;
            }

            $frontIcon = $request->getParam('personalisation_front_icon');
            if ($frontIcon) {
                $result['front_icon'] = $frontIcon;
            }

            $backText = $request->getParam('personalisation_back_text');
            if ($backText) {
                $result['back_text'] = $backText;

                $backTextFontFamily = $request->getParam('personalisation_back_text_font_family');
                if ($backTextFontFamily) {
                    $result['back_font'] = $backTextFontFamily;
                }

                $backTextDirection = $request->getParam('personalisation_back_text_direction');
                if ($backTextDirection) {
                    $result['back_orientation'] = $backTextDirection;
                }
            }
            
            if (count($result)) {
                return $result;
            }
        }
        return null;
    }
}
