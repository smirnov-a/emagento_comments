<?php

namespace Emagento\Comments\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class ReviewActions extends Column
{
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

        foreach ($dataSource['data']['items'] as &$item) {
            $item[$this->getData('name')]['edit'] = [
                'href'   => $this->context->getUrl(
                    'local_comments/comment/edit',
                    ['id' => $item['review_id']]
                ),
                'label'  => __('Edit'),
                'hidden' => false,
            ];
        }

        return $dataSource;
    }
}
