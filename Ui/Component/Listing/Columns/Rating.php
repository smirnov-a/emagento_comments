<?php

namespace Emagento\Comments\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Rating extends Column
{
    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource): array
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['rating']) {
                    $data = [];
                    for ($i = 0; $i < 5; $i++) {
                        if ($i < $item['rating']) {
                            $data[] = 'selected';
                        } else {
                            $data[] = 'notSelected';
                        }
                    }
                    $item['rating'] = $data;
                }
            }
        }

        return parent::prepareDataSource($dataSource);
    }
}
