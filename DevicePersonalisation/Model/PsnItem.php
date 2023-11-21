<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model;

use BAT\DevicePersonalisation\Api\Data\PsnItemDataInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnItem as PsnItemResource;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class PsnItem extends AbstractModel implements IdentityInterface, PsnItemDataInterface
{
    public const CACHE_TAG = 'devicepersonalisation_psnitem';

    /**
     * @var string
     */
    protected $_cacheTag = 'devicepersonalisation_psnitem'; // @codingStandardsIgnoreLine

    /**
     * @var string
     */
    protected $_eventPrefix = 'devicepersonalisation_psnitem'; // @codingStandardsIgnoreLine

    protected function _construct(): void // @codingStandardsIgnoreLine
    {
        $this->_init(PsnItemResource::class);
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getEntityId(): ?int
    {
        return $this->_getData(PsnItemDataInterface::ENTITY_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setEntityId($entityId): PsnItemDataInterface
    {
        return $this->setData(PsnItemDataInterface::ENTITY_ID, $entityId);
    }

    public function getOrderItemId(): ?int
    {
        return $this->_getData(PsnItemDataInterface::ORDER_ITEM_ID);
    }

    public function setOrderItemId(int $orderItemId): PsnItemDataInterface
    {
        return $this->setData(PsnItemDataInterface::ORDER_ITEM_ID, $orderItemId);
    }

    public function getQuoteItemId(): ?int
    {
        return $this->_getData(PsnItemDataInterface::QUOTE_ITEM_ID);
    }

    public function setQuoteItemId(int $quoteItemId): PsnItemDataInterface
    {
        return $this->setData(PsnItemDataInterface::QUOTE_ITEM_ID, $quoteItemId);
    }

    public function getFrontFont(): ?string
    {
        return $this->_getData(PsnItemDataInterface::FRONT_FONT);
    }

    public function setFrontFont(string $frontFont): PsnItemDataInterface
    {
        return $this->setData(PsnItemDataInterface::FRONT_FONT, $frontFont);
    }

    public function getFrontText(): ?string
    {
        return $this->_getData(PsnItemDataInterface::FRONT_TEXT);
    }

    public function setFrontText(string $frontText): PsnItemDataInterface
    {
        return $this->setData(PsnItemDataInterface::FRONT_TEXT, $frontText);
    }

    public function getFrontOrientation(): ?string
    {
        return $this->_getData(PsnItemDataInterface::FRONT_ORIENTATION);
    }

    public function setFrontOrientation(string $frontOrientation): PsnItemDataInterface
    {
        return $this->setData(PsnItemDataInterface::FRONT_ORIENTATION, $frontOrientation);
    }

    public function getFrontPattern(): ?string
    {
        return $this->_getData(PsnItemDataInterface::FRONT_PATTERN);
    }

    public function setFrontPattern(string $frontPattern): PsnItemDataInterface
    {
        return $this->setData(PsnItemDataInterface::FRONT_PATTERN, $frontPattern);
    }

    public function getFrontIcon(): ?string
    {
        return $this->_getData(PsnItemDataInterface::FRONT_ICON);
    }

    public function setFrontIcon(string $frontIcon): PsnItemDataInterface
    {
        return $this->setData(PsnItemDataInterface::FRONT_ICON, $frontIcon);
    }

    public function getBackFont(): ?string
    {
        return $this->_getData(PsnItemDataInterface::BACK_FONT);
    }

    public function setBackFont(string $backFont): PsnItemDataInterface
    {
        return $this->setData(PsnItemDataInterface::BACK_FONT, $backFont);
    }

    public function getBackText(): ?string
    {
        return $this->_getData(PsnItemDataInterface::BACK_TEXT);
    }

    public function setBackText(string $backText): PsnItemDataInterface
    {
        return $this->setData(PsnItemDataInterface::BACK_TEXT, $backText);
    }

    public function getBackOrientation(): ?string
    {
        return $this->_getData(PsnItemDataInterface::BACK_ORIENTATION);
    }

    public function setBackOrientation(string $backOrientation): PsnItemDataInterface
    {
        return $this->setData(PsnItemDataInterface::BACK_ORIENTATION, $backOrientation);
    }

    public function getPersonalisationPrice(): ?float
    {
        return (float) $this->_getData(PsnItemDataInterface::PERSONALISATION_PRICE);
    }

    public function setPersonalisationPrice(float $price): PsnItemDataInterface
    {
        return $this->setData(PsnItemDataInterface::PERSONALISATION_PRICE, $price);
    }

    /**
     * @inheritDoc
     */
    public function getPersonalisationIsFree(): bool
    {
        return $this->_getData(PsnItemDataInterface::PERSONALISATION_IS_FREE) ? true : false;
    }

    /**
     * @inheritDoc
     */
    public function setPersonalisationIsFree(bool $isFree): PsnItemDataInterface
    {
        $this->setData(PsnItemDataInterface::PERSONALISATION_IS_FREE, $isFree);
    }
}
