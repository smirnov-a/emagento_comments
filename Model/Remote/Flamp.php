<?php

namespace Emagento\Comments\Model\Remote;

/**
 * Class for work with Flamp
 */
class Flamp extends \Emagento\Comments\Model\Remote\AbstractRemote
{
    /**
     * Собственно работа по загрузке комментариев
     *
     * @return int
     */
    public function getComments()
    {
        if (!$this->isGlobalEnabled() || !$this->isEnabled()) {
            return 0;
        }
        $cnt = 0;
        $storeId = $this->getStoreId();
        $stores = $this->getStores();
        // сходить за комментариями на Flamp
        $work = $this->doRequest(); //var_dump($work); exit;
        if (!$work || !isset($work['reviews'])) {
            if (isset($work['error_code']) && $work['message']) {
                $this->_logger->info('Error loading Flamp reviews: ' . $work['message']);
            }
            return 0;
        }
        $this->_logger->info('Flamp. Found ' . count($work['reviews']) . ' comments');
        // 'local_comments/settings/rating_id' 6
        $ratingId = $this->getConfigCommonValue('rating_id');
        foreach ($work['reviews'] as $item) {
            //var_dump($item); exit;
            try {
                // если есть текст комментария
                if (empty($item['text'])) {
                    continue;
                }
                // зачистить html в комментарии и в никнэйме
                $msgReview = $this->_escaper->escapeHtml($this->_filterManager->removeTags($item['text']));
                $nick = !empty($item['user']['name'])
                    ? $this->_escaper->escapeHtml($this->_filterManager->removeTags($item['user']['name']))
                    : 'Anonymous';
                // взять по типу и коду флампа
                /** @var \Magento\Review\Model\Review $review */
                $review = $this->_reviewFactory->create();
                $this->_reviewsResource->loadByAttributes(
                    $review,
                    ['source' => 'flamp', 'source_id' => $item['id']]
                );
                //echo 'id: '. $review->getId(); exit;
                if (!$review->getId()) {
                    // отзыва еще нет. добавить с типом "Отзыв к магазину" и сразу "Готов к показу"
                    $review
                        ->setEntityId(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)
                        ->setSource('flamp')
                        ->setSourceId($item['id'])
                        ->setCreatedAt($item['date_created'] ?? $this->dateTime->timestamp())
                        ->setUpdatedAt($item['date_edited'] ?? null)
                        ->setEntityPkValue(0)       // в контексте отзыва о магазине это код магазина
                        ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)
                        ->setTitle('_robot_')              // тайтл пустой
                        ->setDetail($msgReview)     // текст отзыва
                        ->setNickname($nick)
                        ->setStoreId($storeId)
                        ->setStores($stores)
                        ->save();
                    // добавить рейтинг, если есть
                    if (!empty($item['rating'])) {        // там число от 1 до 5
                        $this->_ratingFactory->create()
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            //->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                            ->addOptionVote((int)$item['rating'], $storeId);
                    }
                    $review->aggregate();
                    // log
                    $this->_logger->info('Flamp: save comment id: ' . $item['id']);
                    $cnt++;
                } else {
                    // отзыв уже загружен
                    // в этом случае проверить не поменялися ли комментарий
                    // да, там пользователь может поменять комментарий
                    if (!empty($item['date_edited'])) {
                        // строку '2017-09-01T12:48:35.0+07:00' перевести в число
                        $time = $this->dateTime->timestamp($item['date_edited']);
                        // если дата редактирования с нашей стороны пустая либо пришедшая дата больше нашей
                        if (empty($review->getUpdatedAt()) ||
                            $time > $this->dateTime->timestamp($review->getUpdatedAt())) {
                            // update
                            $review
                                ->setCreatedAt($item['date_created'])
                                ->setUpdatedAt($item['date_edited'])
                                ->setDetail($msgReview)
                                ->setNickname($nick)
                                ->save();
                            $cnt++;
                            $this->_logger->info('Flamp: update comment id: ' . $review->getId());
                        }
                    }
                }
                // проверить есть ли ответ на этот комментарий
                if (!empty($item['official_answer'])) {
                    // добавить с указанием парента
                    // код родительского комментария, для которого данный комментарий является ответом
                    $reviewId = $review->getId();
                    // текст ответа
                    $msgReply = $this->_escaper->escapeHtml(
                        $this->_filterManager->removeTags($item['official_answer']['text'])
                    );
                    if ($reviewId && $msgReply) {
                        // название магазина на Flamp'е
                        $nickReply = $this->_escaper->escapeHtml(
                            $this->_filterManager->removeTags($item['official_answer']['org_name'])
                        );
                        // попробовать подгрузить по коду flamp
                        $reply = $this->_reviewFactory->create();
                        $this->_reviewsResource->loadByAttributes(
                            $reply,
                            ['source' => 'flamp', 'source_id' => $item['official_answer']['id']]
                        );
                        if (!$reply->getId()) {
                            // добавить с типом 4 Отзыв к магазину
                            $reply
                                ->setEntityId(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)
                                ->setSource('flamp')
                                ->setSourceId($item['official_answer']['id'])
                                ->setParentId($reviewId)
                                ->setCreatedAt(
                                    $item['official_answer']['date_created']
                                    ?? $this->dateTime->timestamp()
                                )
                                ->setUpdatedAt($item['official_answer']['date_edited'] ?? null)
                                // в контексте отзыва о магазине это код магазина
                                ->setEntityPkValue(0)
                                ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)
                                ->setTitle('_robot_')
                                ->setDetail($msgReply)
                                ->setNickname($nickReply)
                                ->setStoreId($storeId)
                                ->setStores($stores)
                                ->save();
                            // log
                            $this->_logger->info(
                                'Flamp: save reply id: ' . $item['official_answer']['id'] .
                                ' on parent comment id: ' . $reviewId
                            );
                            $cnt++;
                        } else {
                            // обновить, если дата изменилась
                            if (!empty($item['official_answer']['date_edited'])) {
                                // строку '2017-09-01T12:48:35.0+07:00' перевести в число
                                $time = $this->dateTime->timestamp($item['official_answer']['date_edited']);
                                // если дата редакт. с нашей стороны пустая либо пришедшая дата больше нашей
                                if (empty($reply->getUpdatedAt()) ||
                                    $time > $this->dateTime->timestamp($reply->getUpdatedAt())) {
                                    // update
                                    $reply
                                        ->setCreatedAt($item['official_answer']['date_created'])
                                        ->setUpdatedAt($item['official_answer']['date_edited'])
                                        ->setDetail($msgReply)
                                        ->setNickname($nickReply)
                                        ->save();
                                    $cnt++;
                                    $this->_logger->info(
                                        'Flamp: update reply comment id: ' . $reply->getId()
                                    );
                                }
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->_logger->critical($e->getMessage());
            }
        }

        return $cnt;
    }

    /**
     * Строит ссылку на flamp
     *
     * @return string
     */
    public function getUrl()
    {
        // id взять из конфига
        $id = $this->getConfigValue('flamp_id');
        //$id = $this->_scopeConfig->getValue(
        //    'local_comments/flamp/flamp_id',
        //    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        //);
        // https://api.reviews.2gis.com/2.0/branches/1267165676751243/reviews?limit=24&is_advertiser=false&fields=meta.providers,meta.branch_rating,meta.branch_reviews_count,meta.org_rating,meta.org_reviews_count
        return sprintf('https://api.reviews.2gis.com/2.0/branches/%s/reviews', $id);
    }

    /**
     * GET parameters
     *
     * @return array
     */
    public function getParams()
    {
        return [
            'limit' => 24,
            'is_advertiser' => 'false',
            'fields' => 'meta.providers,meta.branch_rating,meta.branch_reviews_count,meta.org_rating,meta.org_reviews_count',
        ];
    }

    /**
     * Включен ли Flamp в админке 'local_comments/flamp/is_enabled'
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getConfigValue('is_enabled');
        //return (bool)$this->_scopeConfig->getValue(
        //    'local_comments/flamp/is_enabled',
        //    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        //);
    }

    /**
     * Get value from core_config
     *
     * @param string $item
     * @return int|null|string
     */
    public function getConfigValue($item)
    {
        return $this->_scopeConfig->getValue(
            'local_comments/flamp/' . $item,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
