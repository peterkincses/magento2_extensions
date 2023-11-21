<?php

declare(strict_types=1);

namespace BAT\Yoti\Model;

use BAT\Yoti\Helper\Data as YotiHelper;
use Magento\Framework\Module\Dir as ModuleDir;

class YotiDocPemKeyList
{
    /** @var ModuleDir */
    private $moduleDir;

    /**
     * @param $moduleDir
     */
    public function __construct(
        ModuleDir $moduleDir
    ) {
        $this->moduleDir = $moduleDir;
    }

    public function getPemKeyList(): array
    {
        $optionArr = [];
        $dirPath = $this->getCertificatesDirFullPath();
        $dirFilesPattern = $dirPath . '*.pem';

        $files = glob($dirFilesPattern);
        if ($files && count($files) > 0) {
            foreach ($files as $filename) {
                $splitfilePath = explode('/', $filename);
                $filesortName = $splitfilePath[count($splitfilePath) - 1];
                $optionArr[] = ['value' => $filesortName, 'label' => $filesortName];
            }
        }

        return $optionArr;
    }

    public function getCertificatesDirFullPath(): string
    {
        $moduleDir = $this->moduleDir->getDir('BAT_Yoti');
        return $moduleDir . '/' . YotiHelper::DOC_UPLOAD_PEM_LOCATION_DIR . '/';
    }
}
