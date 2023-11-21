<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel\PsnRestrictedWords;

use BAT\DevicePersonalisation\Model\PsnRestrictedWords;
use BAT\DevicePersonalisation\Model\ResourceModel\PsnRestrictedWords as ResourcePsnRestrictedWords;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     *
     * phpcs:disable PSR2.Classes.PropertyDeclaration
     */
    protected $_idFieldName = 'word_id';
    // phpcs:enable

    /**
     * @var string
     *
     * phpcs:disable PSR2.Classes.PropertyDeclaration
     */
    protected $_eventPrefix = 'psn_restricted_words_collection';
    // phpcs:enable

    /**
     * @var string
     *
     * phpcs:disable PSR2.Classes.PropertyDeclaration
     */
    protected $_eventObject = 'psn_restricted_words_collection';
    // phpcs:enable

    /**
     * phpcs:disable PSR2.Methods.MethodDeclaration
     */
    public function _construct(): void
    {
        $this->_init(
            PsnRestrictedWords::class,
            ResourcePsnRestrictedWords::class
        );
    }
    // phpcs:enable
}
