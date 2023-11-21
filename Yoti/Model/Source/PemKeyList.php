<?php

declare(strict_types=1);

namespace BAT\Yoti\Model\Source;

use BAT\Yoti\Model\YotiDocPemKeyList;
use Magento\Framework\Data\OptionSourceInterface;

class PemKeyList implements OptionSourceInterface
{
    /** @var YotiDocPemKeyList */
    protected $yotiDocPemKeyList;

    public function __construct(
        YotiDocPemKeyList $yotiDocPemKeyList
    ) {
        $this->yotiDocPemKeyList = $yotiDocPemKeyList;
    }

    /**
     * @return mixed[] $optionArray
     */
    public function toOptionArray(): array
    {
        return $this->yotiDocPemKeyList->getPemKeyList();
    }
}
