<?php

namespace Emagento\Comments\Ui\Component\Listing\Columns;

use Magento\Ui\Component\Listing\Columns\Column;

class ReviewActions extends Column
{
    /**
     * {@inheritdoc}
     * @since 100.1.0
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            //var_dump($item); exit;
            $item[$this->getData('name')]['edit'] = [
                'href'  => $this->context->getUrl(
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
