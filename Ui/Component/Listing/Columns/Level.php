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
        //var_dump($dataSource); exit;
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                //$product = $this->productloader>create()->load((int)$item[$this->getData('name')]);
                $item['level'] = $this->renderLevelColumn($item);   //'level111';    //$product->getName();
            }
        }
        //var_dump($dataSource); exit;

        return $dataSource;
    }

    /**
     * @param array $item
     * @return string
     */
    protected function renderLevelColumn($item)
    {
        //var_dump($item); exit;
        $level = $item['level'] ?? 1;
        $ret = (string)$level;
        if ($level > 1) {
            if (!empty($item['parent_id'])) {
                $ret .= '<br /><nobr>(reply on: <b>' . $item['parent_id'] . '</b>)</nobr>';
            }
        }

        return $ret;
    }
}
