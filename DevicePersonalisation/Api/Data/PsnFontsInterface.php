<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api\Data;

interface PsnFontsInterface
{
    public const FONT_ID         = 'font_id';
    public const NAME            = 'name';
    public const PREVIEW_TEXT    = 'preview_text';
    public const FONT_FILE       = 'font_file';
    public const FONT_SIZE       = 'font_size';

    /**
     * Retrieve font id
     *
     * @return int|null
     */
    public function getFontId(): ?int;

    /**
     * Set font id
     *
     * @param int $fontId
     * @return $this
     */
    public function setFontId($fontId);

    /**
     * Get name
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set name
     *
     * @param string $name
     * @return $this
     */
    public function setName($name);

    /**
     * Get font file
     *
     * @return string|null
     */
    public function getFontFile(): ?string;

    /**
     * Set font file
     *
     * @param string $fontFile
     * @return $this
     */
    public function setFontFile($fontFile);

    /**
     * Get preview text
     *
     * @return string|null
     */
    public function getPreviewText(): ?string;

    /**
     * Set preview text
     *
     * @param string $previewText
     * @return $this
     */
    public function setPreviewText($previewText);

    /**
     * @param int $storeId
     * @return mixed
     */
    public function getStoreData($storeId);

    /**
     * Get font size
     *
     * @return string|null
     */
    public function getFontSize(): ?string;

    /**
     * Set font size
     *
     * @param string $fontSize
     * @return $this
     */
    public function setFontSize(string $fontSize);
}
