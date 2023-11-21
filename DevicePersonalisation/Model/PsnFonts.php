<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model;

use BAT\DevicePersonalisation\Api\Data\PsnFontsInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnFonts as PsnFontsResource;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class PsnFonts extends AbstractModel implements PsnFontsInterface, IdentityInterface
{
    public const CACHE_TAG = 'psn_fonts'; // Cache tag

    protected function _construct(): void
    {
        $this->_init(PsnFontsResource::class);
        $this->setIdFieldName('font_id');
    }

    /**
     * {@inheritDoc}
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * {@inheritDoc}
     */
    public function getFontId(): ?int
    {
        return (int) $this->_getData(PsnFontsInterface::FONT_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setFontId($fontId)
    {
        return $this->setData(PsnFontsInterface::FONT_ID, $fontId);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->_getData(PsnFontsInterface::NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        return $this->setData(PsnFontsInterface::NAME, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getFontFile(): ?string
    {
        return $this->_getData(PsnFontsInterface::FONT_FILE);
    }

    /**
     * {@inheritDoc}
     */
    public function setFontFile($fontFile)
    {
        return $this->setData(PsnFontsInterface::FONT_FILE, $fontFile);
    }

    /**
     * {@inheritDoc}
     */
    public function getPreviewText(): ?string
    {
        return $this->_getData(PsnFontsInterface::PREVIEW_TEXT);
    }

    /**
     * {@inheritDoc}
     */
    public function setPreviewText($previewText)
    {
        return $this->setData(PsnFontsInterface::PREVIEW_TEXT, $previewText);
    }

    /**
     * {@inheritDoc}
     */
    public function getStoreData($storeId)
    {
        return $this->getResource()->getStoreData((int) $this->getId(), (int) $storeId);
    }

    /**
     * {@inheritDoc}
     */
    public function getFontSize(): ?string
    {
        return $this->_getData(PsnFontsInterface::FONT_SIZE);
    }

    /**
     * {@inheritDoc}
     */
    public function setFontSize(?string $fontSize)
    {
        return $this->setData(PsnFontsInterface::FONT_SIZE, $fontSize);
    }
}
