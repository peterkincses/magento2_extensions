<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Enabled extends Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($this->getData('name') == 'status') {
                    if ($item['status']) {
                        $item['status'] = __('Yes');
                    } else {
                        $item['status'] = __('No');
                    }
                }
            }
        }

        return $dataSource;
    }
}
