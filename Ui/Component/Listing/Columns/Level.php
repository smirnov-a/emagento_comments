<?php

namespace Emagento\Comments\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class Level extends Column
{
    private const TEMPLATE_REPLY = '<br /><nobr>(reply on: <b>%s</b>)</nobr>';

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
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
     * Render Level Column
     *
     * @param array $item
     * @return string
     */
    protected function renderLevelColumn(array $item): string
    {
        $level = $item['level'] ?? 1;
        $ret = (string) $level;
        if ($level > 1 && !empty($item['parent_id'])) {
            $ret .= sprintf(self::TEMPLATE_REPLY, $item['parent_id']);
        }

        return $ret;
    }
}
