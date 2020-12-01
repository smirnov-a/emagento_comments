<?php

namespace Emagento\Comments\Model\Remote;

/**
 * Class for work with Flamp
 */
class Flamp extends AbstractRemote
{
    private const TYPE = 'flamp';

    /**
     * Work with flamp comments
     * @return int
     */
    public function getComments()
    {
        if (!$this->isGlobalEnabled() || !$this->isEnabled()) {
            return 0;
        }
        $cnt = 0;
        // download comments
        $work = $this->doRequest();
        if (!$work || !isset($work['reviews'])) {
            if (isset($work['error_code']) && $work['message']) {
                $this->_logger->info('Error loading Flamp reviews: ' . $work['message']);
            }
            return 0;
        }
        $this->_logger->info('Flamp. Found ' . count($work['reviews']) . ' comments');
        // params for _processItem()
        $params = [
            'store_id' => $this->getStoreId(),
            'stores' => $this->getStores(),
            'rating_id' => $this->getConfigCommonValue('rating_id')
        ];
        foreach ($work['reviews'] as $item) {
            // если есть текст комментария
            if (empty($item['text'])) {
                continue;
            }
            try {
                $res = $this->_processItem($item, $params);
                if ($res) {
                    $cnt++;
                }
            } catch (\Exception $e) {
                $this->_logger->critical($e->getMessage());
            }
        }

        return $cnt;
    }

    /**
     * Process item
     * @param array $item
     * @param array $params
     * @return bool
     * @throws \Exception
     */
    private function _processItem($item, $params)
    {
        $ret = false;
        $storeId = $params['store_id'];
        $stores = $params['stores'];
        $ratingId = $params['rating_id'];
        // clean html in comment and username
        $msgReview = $this->_escaper->escapeHtml($this->_filterManager->removeTags($item['text']));
        $nick = !empty($item['user']['name'])
                ? $this->_escaper->escapeHtml($this->_filterManager->removeTags($item['user']['name']))
                : 'Anonymous';
        // get by flamp id
        /** @var \Magento\Review\Model\Review $review */
        $review = $this->_reviewFactory->create();
        $this->_reviewsResource->loadByAttributes(
            $review,
            ['source' => self::TYPE, 'source_id' => $item['id']]
        );

        if (!$review->getId()) {
            // have no review. add with "Store review" and "Approved"
            $review
                ->setEntityId(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)
                ->setSource(self::TYPE)
                ->setSourceId($item['id'])
                ->setCreatedAt($item['date_created'] ?? $this->dateTime->timestamp())
                ->setUpdatedAt($item['date_edited'] ?? null)
                ->setEntityPkValue(0)       // в контексте отзыва о магазине это код магазина
                ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)
                ->setTitle('_robot_')
                ->setDetail($msgReview)
                ->setNickname($nick)
                ->setStoreId($storeId)
                ->setStores($stores)
                ->save();
            // добавить рейтинг, если есть
            if (!empty($item['rating'])) {        // 1..5
                $this->_ratingFactory->create()
                    ->setRatingId($ratingId)
                    ->setReviewId($review->getId())
                    //->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                    ->addOptionVote((int)$item['rating'], $storeId);
            }
            $review->aggregate();
            $ret = true;
            // log
            $this->_logger->info('Flamp: save comment id: ' . $item['id']);
        } else {
            // have review
            // check if review changed
            // (yes user can modify review)
            if (!empty($item['date_edited'])) {
                // '2017-09-01T12:48:35.0+07:00' convert to time
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
                    $ret = true;
                    $this->_logger->info('Flamp: update comment id: ' . $review->getId());
                }
            }
        }
        // check store answer on review
        if (!empty($item['official_answer'])) {
            // add with parent id
            // parent id for review
            $reviewId = $review->getId();
            // content
            $msgReply = $this->_escaper->escapeHtml(
                $this->_filterManager->removeTags($item['official_answer']['text'])
            );
            if ($reviewId && $msgReply) {
                // store name
                $nickReply = $this->_escaper->escapeHtml(
                    $this->_filterManager->removeTags($item['official_answer']['org_name'])
                );
                // try to load by flamp id
                $reply = $this->_reviewFactory->create();
                $this->_reviewsResource->loadByAttributes(
                    $reply,
                    ['source' => self::TYPE, 'source_id' => $item['official_answer']['id']]
                );
                if (!$reply->getId()) {
                    // add with "Store review"
                    $reply
                        ->setEntityId(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)
                        ->setSource(self::TYPE)
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
                    $ret = true;
                    // log
                    $this->_logger->info(
                        'Flamp: save reply id: ' . $item['official_answer']['id'] .
                        ' on parent comment id: ' . $reviewId
                    );
                } else {
                    // check if date changed
                    if (!empty($item['official_answer']['date_edited'])) {
                        // '2017-09-01T12:48:35.0+07:00' convert to time
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
                            $ret = true;
                            $this->_logger->info(
                                'Flamp: update reply comment id: ' . $reply->getId()
                            );
                        }
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Строит ссылку на flamp
     *
     * @return string
     */
    public function getUrl()
    {
        // get id from store config
        $id = $this->getConfigValue('flamp_id');
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
            'fields' => 'meta.providers,meta.branch_rating,meta.branch_reviews_count,' .
                        'meta.org_rating,meta.org_reviews_count',
        ];
    }

    /**
     * Включен ли Flamp в админке 'local_comments/flamp/is_enabled'
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getConfigValue('is_enabled');
    }

    /**
     * Get value from core_config
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
