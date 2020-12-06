<?php

namespace Emagento\Comments\Model\Remote;

/**
 * Class load Yandex store reviews
 */
class Yandex extends \Emagento\Comments\Model\Remote\AbstractRemote
{
    const TYPE = 'yandex';
    /**
     * Собственно работа по загрузке комментариев
     *
     * @return int
     */
    public function getComments() : int
    {
        if (!$this->isGlobalEnabled() || !$this->isEnabled()) {
            return 0;
        }
        $cnt = 0;
        // download comments
        $work = $this->doRequest();
        if (!empty($work['errors'])) {
            $this->_logger->error('Error loading Yandex reviews: ' . $work['errors'][0]);
            return 0;
        }
        $this->_logger->info('Yandex: Found ' . count($work['shopOpinions']['opinion']) . ' comments');
        //
        foreach ($work['shopOpinions']['opinion'] as $item) {
            if (empty($item['text'])) {
                continue;
            }
            try {
                $res = $this->_processItem($item);
                if ($res) {
                    $cnt += $res;
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
     * @return int
     * @throws \Exception
     */
    private function _processItem($item) : int
    {
        $ret = 0;
        // текст + Достоинства + Недостатки
        $msgReview = !empty($item['text']) ? $this->_escaper->escapeHtml($item['text']) : '';
        $pro = !empty($item['pro']) ? $this->_escaper->escapeHtml($item['pro']) : '';
        $contra = !empty($item['contra']) ? $this->_escaper->escapeHtml($item['contra']) : '';
        // если есть Достоинства
        $msgReview .= strlen($pro) > 1 ?
            ($msgReview ? '<br/>' : '') . '<span>Достоинства: </span>' . $pro
            : '';
        // если есть Недостатки
        $msgReview .= strlen($contra) > 1 ?
            ($msgReview ? '<br/>' : '') . '<span>Недостатки: </span>' . $contra
            : '';
        $msgReview = $this->_filterManager->stripTags(
            $msgReview,
            ['allowableTags' => ['<br>', '<span>'], 'escape' => false]
        );
        // автор
        $nick = (!$item['anonymous'] && !empty($item['author'])) ?
            $this->_escaper->escapeHtml($this->_filterManager->removeTags($item['author']))
            : 'Anonymous';
        // подгрузить запись по яндексовому коду
        /** @var \Magento\Review\Model\Review $review */
        $review = $this->_reviewFactory->create();
        $this->_reviewsResource->loadByAttributes(
            $review,
            ['source' => self::TYPE, 'source_id' => $item['id']]
        );
        $productId = 0;
        $customerId = null;
        if (!$review->getId()) {
            // have no review. add with "Store review" and "Approved"
            $review
                ->setEntityId(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)    // 4 отзыв к магазину
                ->setSource(self::TYPE)
                ->setSourceId($item['id'])
                ->setCreatedAt($item['date'] ?? $this->dateTime->timestamp())     // у Яндекса не редактируется
                ->setEntityPkValue($productId)       // в контексте отзыва о магазине это код магазина
                ->setCustomerId($customerId)
                ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)    // сразу готов к показу
                ->setTitle('_robot_')
                ->setDetail($msgReview)
                ->setNickname($nick)
                ->setStoreId($this->_storeId)
                ->setStores($this->_stores)
                ->save();
            // добавить рейтинг, если есть
            // у Яндекса рейтинг в виде: 2,1,0,-1,-2
            if (!empty($item['grade'])) {
                $rating = (int)$item['grade'] + 2;  // т.е. 4,3,2,1,0
                $_vote = $this->_ratingOptions[$this->_ratingId][$rating] ?? null;
                if ($_vote) {
                    $this->_ratingFactory->create()
                        ->setRatingId($this->_ratingId)
                        ->setReviewId($review->getId())
                        //->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                        ->addOptionVote($_vote, $productId);
                }
            }
            $review->aggregate();
            // log
            $this->_logger->info('Yandex: save comment id: ' . $item['id']);
            $ret++;
        }
        // у Яндекса комментарии не редактируются
        // проверить есть ли ответ на этот комментарий
        if (!empty($item['comments'])) {
            // добавлять с указанием парента
            // код родительского комментария, на который данный комментарий является ответом
            $reviewId = $review->getId();
            // тут массив размером 1 (так задано параметром 'max_comments' => 1)
            foreach ($item['comments'] as $comment) {
                // текст ответа
                $msgReply = $this->_escaper->escapeHtml(
                    $this->_filterManager->stripTags(
                        $comment['body'],
                        ['allowableTags' => ['<br>'], 'escape' => false]
                    )
                );
                $nickReply = !empty($comment['user']['name']) ?
                    $this->_escaper->escapeHtml($comment['user']['name'])
                    : 'Компания';
                // try to load by Yandex id
                $reply = $this->_reviewFactory->create();
                $this->_reviewsResource->loadByAttributes(
                    $reply,
                    ['source' => self::TYPE, 'source_id' => $comment['official_answer']['id']]
                );
                if (!$reply->getId()) {
                    // добавить с типом 4
                    $reply
                        ->setEntityId(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)
                        ->setSource(self::TYPE)
                        ->setSourceId($comment['official_answer']['id'])
                        ->setParentId($reviewId)
                        ->setCreatedAt($comment['updateTimestamp'] ?? $this->dateTime->timestamp())
                        ->setEntityPkValue($productId)       // в контексте отзыва о магазине это код магазина
                        ->setCustomerId($customerId)
                        ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)    // сразу готов к показу
                        ->setTitle('_robot_')
                        ->setDetail($msgReply)
                        ->setNickname($nickReply)
                        ->setStoreId($this->_storeId)
                        ->setStores($this->_stores)
                        ->save();
                    // log
                    $this->_logger->info(
                        'Yandex: save reply id: ' . $comment['official_answer']['id'] .
                        ' on parent comment id: ' . $reviewId
                    );
                    $ret++;
                }
                break;
            }
        }

        return $ret;
    }

    /**
     * Строит ссылку на yandex
     *
     * @return string
     */
    public function getUrl() : string
    {
        // id взять из конфига local_comments/yandex/shop_id'
        $shopId = $this->getConfigValue('shop_id');
        return sprintf('https://api.content.market.yandex.ru/v2/shops/%s/opinions', $shopId);
    }

    /**
     * GET parameters
     *
     * @return array
     */
    public function getParams() : array
    {
        return [
            'count' => 30,
            'sort'  => 'date',
            'how'   => 'desc',
            'max_comments' => 1
        ];
    }
}
