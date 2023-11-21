<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Ui\Component\Listing\Columns\Fonts;

use Magento\Ui\Component\Listing\Columns\Column;

class Enabled extends Column
{
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($this->getData('name') == 'custom_status') {
                    if ($item['custom_status']) {
                        $item['custom_status'] = __('Yes');
                    } else {
                        $item['custom_status'] = __('No');
                    }
                }
            }
        }

        return $dataSource;
    }
}
