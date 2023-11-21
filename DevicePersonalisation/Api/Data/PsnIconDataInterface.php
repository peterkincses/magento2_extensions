<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api\Data;

interface PsnIconDataInterface
{
    public const ICON_ID = 'icon_id';
    public const ICON_NAME = 'name';
    public const IMAGE = 'image';
    public const THUMBNAIL = 'thumbnail';

    /**
     * Retrieve icon id.
     *
     * @return int|null
     */
    public function getIconId(): ?int;

    /**
     * Set icon id.
     *
     * @param int $iconId
     * @return $this
     */
    public function setIconId(int $iconId): self;

    /**
     * Retrieve name.
     *
     * @return string|null
     */
    public function getIconName(): ?string;

    /**
     * Set icon name.
     *
     * @param string $iconName
     * @return $this
     */
    public function setIconName(string $iconName): self;

    /**
     * Retrieve image.
     *
     * @return string|null
     */
    public function getImage(): ?string;

    /**
     * Set icon image.
     *
     * @param string $iconImage
     * @return $this
     */
    public function setImage(string $iconImage): self;

    /**
     * Retrieve thumbnail.
     *
     * @return string|null
     */
    public function getThumbnail(): ?string;

    /**
     * Set icon thumbnail.
     *
     * @param string $thumbnailImage
     * @return $this
     */
    public function setThumbnail(string $thumbnailImage): self;
}
