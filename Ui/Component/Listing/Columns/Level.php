<?php

namespace Emagento\Comments\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Level extends Column
{
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $item['level'] = $this->renderLevelColumn($item);
            }
        }

        return $dataSource;
    }

    /**
     * @param array $item
     * @return string
     */
    protected function renderLevelColumn($item)
    {
        $level = $item['level'] ?? 1;
        $ret = (string) $level;
        if ($level > 1) {
            if (!empty($item['parent_id'])) {
                $ret .= '<br /><nobr>(reply on: <b>' . $item['parent_id'] . '</b>)</nobr>';
            }
        }

        return $ret;
    }
}
