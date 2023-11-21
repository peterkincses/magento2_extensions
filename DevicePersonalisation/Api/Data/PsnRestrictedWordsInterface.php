<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Api\Data;

interface PsnRestrictedWordsInterface
{
    public const WORD_ID = 'word_id';
    public const STORE_ID = 'store_id';
    public const RESTRICTED_WORD = 'restricted_word';

    /**
     * Get word id
     *
     * @return int
     */
    public function getWordId(): int;

    /**
     * Set word id
     *
     * @param int $wordId
     * @return \BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface
     */
    public function setWordId(int $wordId): self;

    /**
     * Get store id
     *
     * @return int
     */
    public function getStoreId(): int;

    /**
     * Set store id
     *
     * @param int $storeId
     * @return \BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface
     */
    public function setStoreId(int $storeId): self;

    /**
     * Get restricted word
     *
     * @return string
     */
    public function getRestrictedWord(): string;

    /**
     * Set restricted word
     *
     * @param string $restrictedWord
     * @return \BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsInterface
     */
    public function setRestrictedWord(string $restrictedWord): self;
}
