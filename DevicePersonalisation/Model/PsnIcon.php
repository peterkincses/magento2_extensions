<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model;

use BAT\DevicePersonalisation\Api\Data\PsnIconDataInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnIcon as ResourcePsnIcon;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class PsnIcon extends AbstractModel implements IdentityInterface, PsnIconDataInterface
{
    public const STATUS_ENABLED = 1;
    public const STATUS_DISABLED = 0;
    public const CACHE_TAG = 'devicepersonalisation_psnicon';

    /**
     * @var string
     */
    protected $_cacheTag = 'devicepersonalisation_psnicon';

    /**
     * @var string
     */
    protected $_eventPrefix = 'devicepersonalisation_psnicon';

    protected function _construct(): void
    {
        $this->_init(ResourcePsnIcon::class);
    }

    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getIconId(): int
    {
        return (int) $this->getData(self::ICON_ID);
    }

    public function getIconName(): ?string
    {
        return $this->getData(self::ICON_NAME);
    }

    public function getImage(): string
    {
        return $this->getData(self::IMAGE);
    }

    public function getThumbnail(): string
    {
        return $this->getData(self::THUMBNAIL);
    }

    public function setIconId(int $iconId): PsnIconDataInterface
    {
        return $this->setData(self::ICON_ID, $iconId);
    }

    public function setIconName(string $iconName): PsnIconDataInterface
    {
        return $this->setData(self::ICON_NAME, $iconName);
    }

    public function setImage(string $iconImage): PsnIconDataInterface
    {
        return $this->setData(self::IMAGE, $iconImage);
    }

    public function setThumbnail(string $thumbnailImage): PsnIconDataInterface
    {
        return $this->setData(self::THUMBNAIL, $thumbnailImage);
    }

    public function getStoreId(): ?int
    {
        return (int) $this->getData('store_id');
    }

    public function getAvailableStatuses(): array
    {
        return [self::STATUS_ENABLED => __('Yes'), self::STATUS_DISABLED => __('No')];
    }
}
