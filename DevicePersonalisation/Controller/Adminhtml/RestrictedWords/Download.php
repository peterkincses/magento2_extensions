<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Controller\Adminhtml\RestrictedWords;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Filesystem;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Math\Random;
use Magento\Store\Model\System\Store as SystemStore;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;

/**
 * Download store data
 */
class Download extends Action
{
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * System store
     *
     * @var SystemStore
     */
    private $systemStore;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var Random
     */
    private $random;

    public function __construct(
        Context $context,
        Filesystem $filesystem,
        SystemStore $systemStore,
        FileFactory $fileFactory,
        Random $random
    ) {
        parent::__construct($context);

        $this->filesystem = $filesystem;
        $this->systemStore = $systemStore;
        $this->fileFactory = $fileFactory;
        $this->random = $random;
    }

    public function execute(): ResponseInterface
    {
        $fileName = sprintf('storelist-%s.csv', $this->random->getRandomString(8));
        $writer = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        $file = $writer->openFile($fileName, 'w');
        try {
            $file->lock();
            try {
                $file->writeCsv(['store_id', 'code', 'name']);
                foreach ($this->systemStore->getStoreCollection() as $store) {
                    $file->writeCsv([$store->getStoreId(), $store->getCode(), $store->getName()]);
                }
            } finally {
                $file->unlock();
            }
        } finally {
            $file->close();
        }

        return $this->fileFactory->create(
            'storelist.csv',
            [
                'type' => 'filename',
                'value' => $fileName,
                'rm' => 1,
            ],
            DirectoryList::TMP
        );
    }
}
