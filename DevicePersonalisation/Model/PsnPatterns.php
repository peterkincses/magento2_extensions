<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model;

use BAT\DevicePersonalisation\Api\Data\PsnPatternsInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnPatterns as PsnPatternsResource;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class PsnPatterns
 */
class PsnPatterns extends AbstractModel implements PsnPatternsInterface, IdentityInterface
{
    public const CACHE_TAG = 'psn_patterns'; // Cache tag

    /**
     * {@inheritDoc}
     */
    protected function _construct()
    {
        $this->_init(PsnPatternsResource::class);
        $this->setIdFieldName('pattern_id');
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
    public function getPatternId(): ?int
    {
        return (int) $this->_getData(PsnPatternsInterface::PATTERN_ID);
    }

    /**
     * {@inheritDoc}
     */
    public function setPatternId($patternId)
    {
        return $this->setData(PsnPatternsInterface::PATTERN_ID, $patternId);
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): ?string
    {
        return $this->_getData(PsnPatternsInterface::NAME);
    }

    /**
     * {@inheritDoc}
     */
    public function setName($name)
    {
        return $this->setData(PsnPatternsInterface::NAME, $name);
    }

    /**
     * {@inheritDoc}
     */
    public function getImage(): ?string
    {
        return $this->_getData(PsnPatternsInterface::IMAGE);
    }

    /**
     * {@inheritDoc}
     */
    public function setImage($image)
    {
        return $this->setData(PsnPatternsInterface::IMAGE, $image);
    }

    /**
     * {@inheritDoc}
     */
    public function getStoreData($storeId)
    {
        return $this->getResource()->getStoreData((int) $this->getId(), (int) $storeId);
    }

    public function getThumbnail(): string
    {
        return $this->getData(self::THUMBNAIL);
    }

    public function setThumbnail(string $thumbnailImage): PsnPatternsInterface
    {
        return $this->setData(self::THUMBNAIL, $thumbnailImage);
    }
}
