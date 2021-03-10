<?php

namespace Emagento\Comments\Model\ResourceModel\Review;

use Magento\Review\Model\ResourceModel\Review\Collection as MagentoCollection;

class Collection extends MagentoCollection
{
    /**
     * Устанавливает фильт по типу комментариев
     * @return $this
     */
    public function addStoreReviewFilter()
    {
        $this->addFieldToFilter(
            'main_table.entity_id',
            ['eq' => \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE]
        );

        return $this;
    }

    /**
     * Добавляет в запрос ответы на отзывы. По одному отзыву для первого уровня
     * @param int $page
     * @param int $limit кол-во комментариев
     * @param bool $isRand в случайном порядке (по умолчанию по дате в обратном порядке)
     * @return Collection
     */
    public function addReviewReplyOneLevel($page = 1, $limit = 5, $isRand = false)
    {
        $offset = $limit * ($page - 1);
        // нужно заджойнить таблицу на себя по полю parent_id
        // в колонках r_xxx будут данные ответа на отзыв (если есть не пустые)
        $this->addStoreReviewFilter()   // а надо ли фильтровать по складу
            ->addFieldToFilter(
                'main_table.status_id',
                \Magento\Review\Model\Review::STATUS_APPROVED
            )
            // комментарии пользователей на первом уровне
            ->addFieldToFilter('main_table.level', 1)
            //->setCurPage($page)
            //->setOrder('review_id', 'ASC')
            //->setPageSize($count)
            // дальше руками
            ->getSelect()
            // ответы магазина на втором уровне (если цепочка комментарий-ответ ниже то сюда не попадет)
            ->joinLeft(
                ['main_table2' => 'review'],
                'main_table.review_id = main_table2.parent_id AND main_table2.level=2',
                [
                    'r_review_id' => 'review_id',
                    'r_level' => 'level',
                ]
            )
            ->joinLeft(
                ['detail2' => 'review_detail'],
                'main_table2.review_id = detail2.review_id',
                [
                    'r_detail_d' => 'detail_id',        // брать колонки из review_detail и добавлять в имя 'r_'
                    'r_title' => 'title',
                    'r_detail' => 'detail',
                    'r_nickname' => 'nickname',
                    'r_customer_id' => 'customer_id',
                ]
            )
            ->limit($limit, $offset);

        if ($isRand) {
            $this->getSelect()->orderRand('review_id');
        } else {
            $this->setOrder('review_id', 'DESC');
        }
        //echo $this->getSelect(); exit;
        return $this;
    }
}
