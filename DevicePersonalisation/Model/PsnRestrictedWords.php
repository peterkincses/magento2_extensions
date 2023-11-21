<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model;

use BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnRestrictedWords as ResourcePsnRestrictedWords;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class PsnRestrictedWords extends AbstractModel implements IdentityInterface, PsnRestrictedWordsInterface
{
    public const CACHE_TAG = 'psn_restrictedwords';

    /**
     * @var string
     *
     * phpcs:disable PSR2.Classes.PropertyDeclaration
     */
    protected $_cacheTag = 'psn_restrictedwords';
    // php:enable

    /**
     * @var string
     *
     * phpcs:disable PSR2.Classes.PropertyDeclaration
     */
    protected $_eventPrefix = 'psn_restrictedwords';
    // php:enable

    /**
     * phpcs:disable PSR2.Methods.MethodDeclaration
     */
    protected function _construct(): void
    {
        $this->_init(ResourcePsnRestrictedWords::class);
    }
    // phpcs:enable

    /**
     * @return string[]
     */
    public function getIdentities(): array
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getWordId(): int
    {
        return (int) $this->getData(self::WORD_ID);
    }

    public function setWordId(int $wordId): PsnRestrictedWordsInterface
    {
        return $this->setData(self::WORD_ID, $wordId);
    }

    public function getStoreId(): int
    {
        return (int) $this->getData(self::STORE_ID);
    }

    public function setStoreId(int $storeId): PsnRestrictedWordsInterface
    {
        return $this->setData(self::STORE_ID, $storeId);
    }

    public function getRestrictedWord(): string
    {
        return (string) $this->getData(self::RESTRICTED_WORD);
    }

    public function setRestrictedWord(string $restrictedWord): PsnRestrictedWordsInterface
    {
        return $this->setData(self::RESTRICTED_WORD, $restrictedWord);
    }
}
