<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api\Data;

interface PsnPatternsInterface
{
    public const TABLE             = 'psn_patterns'; // Db table
    public const TABLE_OVERRIDES   = 'psn_patterns_overrides'; // Db table

    public const PATTERN_ID         = 'pattern_id';
    public const NAME               = 'name';
    public const IMAGE              = 'image';
    public const THUMBNAIL          = 'thumbnail';

    /**
     * @return int
     */
    public function getPatternId(): ?int;

    /**
     * @param int $patternId
     */
    public function setPatternId($patternId);

    /**
     * @return string
     */
    public function getName(): ?string;

    /**
     * @param string $name
     */
    public function setName($name);

    /**
     * @return string
     */
    public function getImage(): ?string;

    /**
     * @param string $image
     */
    public function setImage($image);

    /**
     * @param $storeId
     * @return mixed
     */
    public function getStoreData($storeId);

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
