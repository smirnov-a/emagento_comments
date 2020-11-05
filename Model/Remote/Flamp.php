<?php

namespace Local\Comments\Model\Remote;

/**
 * Class Flamp
 */
class Flamp extends \Local\Comments\Model\Remote\AbstractRemote
{
    /**
     * Собственно работа по загрузке комментариев
     *
     * @return int
     */
    public function getComments()
    {
        $cnt = 0;
        if ($this->isGlobalEnabled() && $this->isEnabled()) {
            $storeId = $this->getStoreId();
            $stores = $this->getStores();
            // сходить за комментариями на Flamp
            $work = $this->doRequest();
            if ($work && isset($work['reviews'])) {
                $this->_logger->info('Flamp. Found ' . count($work['reviews']) . ' comments');
                foreach ($work['reviews'] as $item) {
                    try {
                        // если есть текст комментария
                        if (!empty($item['text'])) {
                            // зачистить html в комментарии и в никнэйме
                            $msgReview = $this->_escaper->escapeHtml($this->_filterManager->removeTags($item['text']));
                            $nick = !empty($item['user']['name']) ? $this->_escaper->escapeHtml($this->_filterManager->removeTags($item['user']['name'])) : 'Anonymous';
                            // взять по типу и коду флампа
                            /** @var \Magento\Review\Model\Review $review */
                            $review = $this->_reviewFactory->create();
                            $this->_reviewsResource->loadByAttributes($review, ['source' => 'flamp', 'source_id' => $item['id']]);
                            //echo $review->getId(); exit;
                            if (!$review->getId()) {
                                // отзыва еще нет. добавить
                                $review
                                    ->setEntityId(\Local\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)    // 4 отзыв к магазину
                                    ->setSource('flamp')
                                    ->setSourceId($item['id'])
                                    ->setCreatedAt($item['date_created'] ?? $this->dateTime->timestamp())
                                    ->setUpdatedAt($item['date_edited'] ?? null)
                                    ->setEntityPkValue(0)       // в контексте отзыва о магазине это код магазина
                                    ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)    // сразу готов к показу
                                    ->setTitle('')              // тайтл пустой
                                    ->setDetail($msgReview)     // текст отзыва
                                    ->setNickname($nick)
                                    ->setStoreId($storeId)
                                    ->setStores($stores)
                                    ->save();
                                // добавить рейтинг, если есть
                                if (!empty($item['rating'])) {        // там число от 1 до 5
                                    $this->_ratingFactory->create()
                                        ->setRatingId(1)                // Quality
                                        ->setReviewId($review->getId())
                                        //->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                                        ->addOptionVote((int)$item['rating'], $storeId);    //$product->getId());
                                }
                                $review->aggregate();
                                // log
                                $this->_logger->info('Flamp: save comment id: ' . $item['id']);
                                $cnt++;
                            } else {
                                // отзыв уже загружен
                                // в этом случае проверить не поменялися ли комментарий (да там пользователь может поменять комментарий)
                                if (!empty($item['date_edited'])) {
                                    $time = $this->dateTime->timestamp($item['date_edited']);   // строку '2017-09-01T12:48:35.0+07:00' перевести в число
                                    // если дата редактирования с нашей стороны пустая либо пришедшая дата больше нашей
                                    if (empty($review->getUpdatedAt()) or $time > $this->dateTime->timestamp($review->getUpdatedAt())) {
                                        // обновить поля
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
                                $msgReply = $this->_escaper->escapeHtml($this->_filterManager->removeTags($item['official_answer']['text']));
                                if ($reviewId and $msgReply) {
                                    $nickReply = $this->_escaper->escapeHtml($this->_filterManager->removeTags($item['official_answer']['org_name']));  // название магазина на Flamp
                                    // попробовать подгрузить по коду flamp
                                    $reply = $this->_reviewFactory->create();
                                    $this->_reviewsResource->loadByAttributes($reply, ['source' => 'flamp', 'source_id' => $item['official_answer']['id']]);
                                    if (!$reply->getId()) {
                                        // добавить
                                        $reply
                                            ->setEntityId(\Local\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)    // 4 отзыв к магазину
                                            ->setSource('flamp')
                                            ->setSourceId($item['official_answer']['id'])
                                            ->setParentId($reviewId)
                                            ->setCreatedAt($item['official_answer']['date_created'] ?? $this->dateTime->timestamp())
                                            ->setUpdatedAt($item['official_answer']['date_edited'] ?? null)
                                            ->setEntityPkValue(0)       // в контексте отзыва о магазине это код магазина
                                            ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)    // сразу готов к показу
                                            ->setTitle('')              // тайтл пустой
                                            ->setDetail($msgReply)      // текст отзыва
                                            ->setNickname($nickReply)
                                            ->setStoreId($storeId)
                                            ->setStores($stores)
                                            ->save();
                                        // log
                                        $this->_logger->info('Flamp: save reply id: ' . $item['official_answer']['id'] . ' on parent comment id: ' . $reviewId);
                                        $cnt++;
                                    } else {
                                        // обновить, если дата изменилась
                                        if (!empty($item['official_answer']['date_edited'])) {
                                            $time = $this->dateTime->timestamp($item['official_answer']['date_edited']);    // строку '2017-09-01T12:48:35.0+07:00' перевести в число
                                            // если дата редактирования с нашей стороны пустая либо пришедшая дата больше нашей
                                            if (empty($reply->getUpdatedAt()) or $time > $this->dateTime->timestamp($reply->getUpdatedAt())) {
                                                // обновить поля
                                                $reply
                                                    ->setCreatedAt($item['official_answer']['date_created'])
                                                    ->setUpdatedAt($item['official_answer']['date_edited'])
                                                    ->setDetail($msgReply)
                                                    ->setNickname($nickReply)
                                                    ->save();
                                                $cnt++;
                                                $this->_logger->info('Flamp: update reply comment id: ' . $reply->getId());
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $this->_logger->critical($e->getMessage());
                    }
                }
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
        $id = $this->_scopeConfig->getValue('local_comments/flamp/flamp_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
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
     * Включен ли Flamp в админке
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->_scopeConfig->getValue('local_comments/flamp/is_enabled', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
}
