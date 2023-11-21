<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Plugin\MatrixRates;

use Mageside\ShippingMatrixRates\Model\ResourceModel\Carrier\Import;

class ImportPlugin
{
    /**
     * @param array|mixed $result
     * @return array|mixed
     */
    public function afterGetTableColumns(Import $subject, $result)
    {
        $result['exclude_personalisation'] = 'Exclude Personalisation';
        return $result;
    }
}
