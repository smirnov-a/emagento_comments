<?php

namespace Emagento\Comments\Model\Remote;

/**
 * Class load Yandex store reviews
 */
class Yandex extends \Emagento\Comments\Model\Remote\AbstractRemote
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
            // сходить за комментариями на Yandex
            $work = $this->doRequest();
            if (!empty($work['errors'])) {
                $this->_logger->error('Yandex error: ' . $work['errors'][0]);
                return 0;
            }
            $this->_logger->info('Yandex: found ' . count($work['shopOpinions']['opinion']) . ' comments');
            // 'local_comments/settings/rating_id' 6
            $ratingId = $this->getConfigCommonValue('rating_id');
            foreach ($work['shopOpinions']['opinion'] as $item) {
                // $item это массив
                //echo $item['id'].'; '.$item['text'].'<br/>';
                // записать отзыв, если его еще нет
                // подгрузить запись по яндексовому коду
                /** @var \Magento\Review\Model\Review $review */
                $review = $this->_reviewFactory->create();
                $this->_reviewsResource->loadByAttributes($review, ['source' => 'yandex', 'source_id' => $item['id']]);
                if (!$review->getId()) {
                    // отзыва еще нет. добавить
                    // текст + Достоинства + Недостатки
                    $msgReview = !empty($item['text']) ? $this->_escaper->escapeHtml($item['text']) : '';
                    $pro       = !empty($item['pro']) ? $this->_escaper->escapeHtml($item['pro']) : '';
                    $contra    = !empty($item['contra']) ? $this->_escaper->escapeHtml($item['contra']) : '';
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
                    $review
                        ->setEntityId(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)    // 4 отзыв к магазину
                        ->setSource('yandex')
                        ->setSourceId($item['id'])
                        ->setCreatedAt($item['date'] ?? $this->dateTime->timestamp())     // у Яндекса не редактируется
                        ->setEntityPkValue(0)       // в контексте отзыва о магазине это код магазина
                        ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)    // сразу готов к показу
                        ->setTitle('_robot_')
                        ->setDetail($msgReview)
                        ->setNickname($nick)
                        ->setStoreId($storeId)
                        ->setStores($stores)
                        ->save();
                    // добавить рейтинг, если есть
                    // у Яндекса рейтинг в таком виде 2,1,0,-1,-2
                    if (!empty($item['grade'])) {
                        $rating = (int)$item['grade'] + 3;  // т.е. 5,4,3,2,1
                        $this->_ratingFactory->create()
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            //->setCustomerId(Mage::getSingleton('customer/session')->getCustomerId())
                            ->addOptionVote($rating, $storeId);
                    }
                    $review->aggregate();
                    // log
                    $this->_logger->info('Yandex: save comment id: ' . $item['id']);
                    $cnt++;
                }
                // у Яндекса комментарии не редактируются

                // проверить есть ли ответ на этот комментарий
                if (!empty($item['comments'])) {
                    // добавлять с указанием парента
                    // код родительского комментария, на который данный комментарий является ответом
                    $reviewId = $review->getId();
                    // тут массив размером 1 (так задано параметром 'max_comments' => 1)
                    foreach ($item['comments'] as $item) {
                        // текст ответа
                        $msgReply = $this->_escaper->escapeHtml(
                            $this->_filterManager->stripTags(
                                $item['body'],
                                ['allowableTags' => ['<br>'], 'escape' => false]
                            )
                        );
                        $nickReply = !empty($item['user']['name']) ?
                            $this->_escaper->escapeHtml($item['user']['name'])
                            : 'Компания';
                        // проверить на уже существующий комментарий
                        // попробовать подгрузить по id
                        $reply = $this->_reviewFactory->create();
                        $this->_reviewsResource->loadByAttributes(
                            $reply,
                            ['source' => 'yandex', 'source_id' => $item['official_answer']['id']]
                        );
                        if (!$reply->getId()) {
                            // добавить с типом 4
                            $reply
                                ->setEntityId(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)
                                ->setSource('yandex')
                                ->setSourceId($item['official_answer']['id'])
                                ->setParentId($reviewId)
                                ->setCreatedAt($item['updateTimestamp'] ?? $this->dateTime->timestamp())
                                ->setEntityPkValue(0)       // в контексте отзыва о магазине это код магазина
                                ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)    // сразу готов к показу
                                ->setTitle('_robot_')
                                ->setDetail($msgReply)
                                ->setNickname($nickReply)
                                ->setStoreId($storeId)
                                ->setStores($stores)
                                ->save();
                            // log
                            $this->_logger->info(
                                'Yandex: save reply id: ' . $item['official_answer']['id'] .
                                ' on parent comment id: ' . $reviewId
                            );
                            $cnt++;
                        }
                        // после первого эл-та выйти (там был косяк, что несколько комментариев попадало)
                        break;
                    }
                }
            }
        }

        return $cnt;
    }

    /**
     * Строит ссылку на yandex
     *
     * @return string
     */
    public function getUrl()
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
    public function getParams()
    {
        return [
            'count' => 30,
            'sort'  => 'date',
            'how'   => 'desc',
            'max_comments' => 1
        ];
    }

    /**
     * Включен ли Yandex в админке
     *
     * @return bool
     */
    public function isEnabled()
    {
        return (bool)$this->getConfigValue('is_enabled');
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
            'local_comments/yandex/' . $item,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
