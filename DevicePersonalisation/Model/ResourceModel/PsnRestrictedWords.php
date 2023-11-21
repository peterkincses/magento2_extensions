<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model\ResourceModel;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Validator\Exception as ValidatorException;

class PsnRestrictedWords extends AbstractDb
{
    /**
     * phpcs:disable PSR2.Methods.MethodDeclaration
     */
    protected function _construct(): void
    {
        $this->_init('psn_restricted_words', 'word_id');
    }
    // phpcs:enable

    /**
     * phpcs:disable PSR2.Methods.MethodDeclaration
     */
    protected function _beforeSave(AbstractModel $object): self
    {
        $connection = $this->getConnection();

        $select = $connection->select()
            ->from($this->getMainTable(), 'word_id')
            ->where('restricted_word = :restricted_word COLLATE utf8_bin')
            ->where('store_id = :store_id');

        $result = $connection->fetchOne(
            $select,
            [
                ':restricted_word' => $object->getRestrictedWord(),
                ':store_id' => $object->getStoreId(),
            ]
        );

        if ($result) {
            throw new ValidatorException(__('Restricted word already exists for this store.'));
        }

        return $this;
    }
    // phpcs:enable
}
