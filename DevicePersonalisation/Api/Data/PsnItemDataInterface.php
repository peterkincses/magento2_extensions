<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api\Data;

interface PsnItemDataInterface
{
    public const ENTITY_ID      = 'entity_id';
    public const ORDER_ITEM_ID  = 'order_item_id';
    public const QUOTE_ITEM_ID  = 'quote_item_id';
    public const FRONT_FONT     = 'front_font';
    public const FRONT_TEXT     = 'front_text';
    public const FRONT_ORIENTATION = 'front_orientation';
    public const FRONT_PATTERN  = 'front_pattern';
    public const FRONT_ICON     = 'front_icon';
    public const BACK_FONT      = 'back_font';
    public const BACK_TEXT      = 'back_text';
    public const BACK_ORIENTATION = 'back_orientation';
    public const PERSONALISATION_PRICE = 'personalisation_price';
    public const PERSONALISATION_TAX = 'personalisation_tax';
    public const PERSONALISATION_IS_FREE = 'personalisation_is_free';

    /**
     * Retrieve entity id.
     *
     * @return int|null
     */
    public function getEntityId(): ?int;

    /**
     * Set icon id.
     *
     * @param int $entityId
     * @return $this
     */
    public function setEntityId($entityId): self;

    /**
     * Retrieve order item id
     *
     * @return int|null
     */
    public function getOrderItemId(): ?int;

    /**
     * Set order item id.
     *
     * @param int $orderItemId
     * @return $this
     */
    public function setOrderItemId(int $orderItemId): self;

    /**
     * Retrieve quote item id
     *
     * @return int|null
     */
    public function getQuoteItemId(): ?int;

    /**
     * Set quote item id.
     *
     * @param int $quoteItemId
     * @return $this
     */
    public function setQuoteItemId(int $quoteItemId): self;

    /**
     * Retrieve front font
     *
     * @return string|null
     */
    public function getFrontFont(): ?string;

    /**
     * Set front font
     *
     * @param string $frontFont
     * @return $this
     */
    public function setFrontFont(string $frontFont): self;

    /**
     * Retrieve text font
     *
     * @return string|null
     */
    public function getFrontText(): ?string;

    /**
     * Set front text
     *
     * @param string $frontText
     * @return $this
     */
    public function setFrontText(string $frontText): self;

    /**
     * Retrieve front orientation
     *
     * @return string|null
     */
    public function getFrontOrientation(): ?string;

    /**
     * Set front orientation
     *
     * @param string $frontOrientation
     * @return $this
     */
    public function setFrontOrientation(string $frontOrientation): self;

    /**
     * Retrieve front Pattern
     *
     * @return string|null
     */
    public function getFrontPattern(): ?string;

    /**
     * Set front Pattern
     *
     * @param string $frontPattern
     * @return $this
     */
    public function setFrontPattern(string $frontPattern): self;

    /**
     * Retrieve front icon
     *
     * @return string|null
     */
    public function getFrontIcon(): ?string;

    /**
     * Set front font
     *
     * @param string $frontIcon
     * @return $this
     */
    public function setFrontIcon(string $frontIcon): self;

    /**
     * Retrieve back font
     *
     * @return string|null
     */
    public function getBackFont(): ?string;

    /**
     * Set back font
     *
     * @param string $backFont
     * @return $this
     */
    public function setBackFont(string $backFont): self;

    /**
     * Retrieve back text
     *
     * @return string|null
     */
    public function getBackText(): ?string;

    /**
     * Set back text
     *
     * @param string $backText
     * @return $this
     */
    public function setBackText(string $backText): self;

    /**
     * Retrieve back orientation
     *
     * @return string|null
     */
    public function getBackOrientation(): ?string;

    /**
     * Set back orientation
     *
     * @param string $backOrientation
     * @return $this
     */
    public function setBackOrientation(string $backOrientation): self;

    /**
     * Retrieve personalisation price
     *
     * @return float|null
     */
    public function getPersonalisationPrice(): ?float;

    /**
     * Set personalisation price
     *
     * @param float $price
     * @return $this
     */
    public function setPersonalisationPrice(float $price): self;

    /**
     * Retrieve personalisation is free
     *
     * @return bool
     */
    public function getPersonalisationIsFree(): bool;

    /**
     * Set personalisation is free
     *
     * @param float $price
     * @return $this
     */
    public function setPersonalisationIsFree(bool $isFree): self;
}
