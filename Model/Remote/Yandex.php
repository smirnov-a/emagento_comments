<?php

namespace Emagento\Comments\Model\Remote;

/**
 * Class load Yandex store reviews
 */
class Yandex extends \Emagento\Comments\Model\Remote\AbstractRemote
{
    const TYPE = 'yandex';

    /**
     * Get comments
     *
     * @return int
     */
    public function getComments() : int
    {
        if (!$this->isGlobalEnabled() || !$this->isEnabled()) {
            return 0;
        }

        $this->fillRatingOptions();
        $cnt = 0;

        // download comments
        if (!$this->_workData) {
            $this->setWorkData($this->doRequest());
        }
        if (!empty($this->_workData['errors'])) {
            $this->_logger->error('Error loading Yandex reviews: ' . $this->_workData['errors'][0]);
            return 0;
        }
        $this->_logger->info('Yandex: Found ' . count($this->_workData['shopOpinions']['opinion']) . ' comments');

        foreach ($this->_workData['shopOpinions']['opinion'] as $item) {
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

        $msgReview = !empty($item['text'])   ? $this->_escaper->escapeHtml($item['text'])   : '';
        $pro       = !empty($item['pro'])    ? $this->_escaper->escapeHtml($item['pro'])    : '';
        $contra    = !empty($item['contra']) ? $this->_escaper->escapeHtml($item['contra']) : '';

        $msgReview .= strlen($pro) > 1
            ? ($msgReview ? '<br/>' : '') . '<span>' . __('Advantages') . ': </span>' . $pro
            : '';
        $msgReview .= strlen($contra) > 1
            ? ($msgReview ? '<br/>' : '') . '<span>' . __('Limitations') . ': </span>' . $contra
            : '';
        $msgReview = $this->_filterManager->stripTags(
            $msgReview,
            ['allowableTags' => ['<br>', '<span>'], 'escape' => false]
        );

        $nick = (!$item['anonymous'] && !empty($item['author']))
            ? $this->_escaper->escapeHtml($this->_filterManager->removeTags($item['author']))
            : 'Anonymous';

        // load by Yandex code
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
                ->setEntityId(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)
                ->setSource(self::TYPE)
                ->setSourceId($item['id'])
                ->setCreatedAt($item['date'] ?? $this->dateTime->timestamp())     // not editable
                ->setEntityPkValue($productId)
                ->setCustomerId($customerId)
                ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)
                ->setTitle('_robot_')
                ->setDetail($msgReview)
                ->setNickname($nick)
                ->setStoreId($this->_storeId)
                ->setStores($this->_stores)
                ->save();

            // yandex rating: 2,1,0,-1,-2
            if (!empty($item['grade'])) {
                $rating = (int) $item['grade'] + 2;  // 4,3,2,1,0
                $_vote = $this->_ratingOptions[$this->_ratingId][$rating] ?? null;
                if ($_vote) {
                    $this->_ratingFactory->create()
                        ->setRatingId($this->_ratingId)
                        ->setReviewId($review->getId())
                        ->addOptionVote($_vote, $productId);
                }
            }
            $review->aggregate();

            $this->_logger->info('Yandex: save comment id: ' . $item['id']);
            $ret++;
        }
        // Yandex don't permit editing reviews
        // check reply on this review
        if (!empty($item['comments'])) {
            // add with parent
            $reviewId = $review->getId();
            foreach ($item['comments'] as $comment) {
                $msgReply = $this->_escaper->escapeHtml(
                    $this->_filterManager->stripTags(
                        $comment['body'],
                        ['allowableTags' => ['<br>'], 'escape' => false]
                    )
                );
                $nickReply = !empty($comment['user']['name'])
                    ? $this->_escaper->escapeHtml($comment['user']['name'])
                    : __('Company');
                // try to load by Yandex id
                $reply = $this->_reviewFactory->create();
                $this->_reviewsResource->loadByAttributes(
                    $reply,
                    ['source' => self::TYPE, 'source_id' => $comment['official_answer']['id']]
                );
                if (!$reply->getId()) {
                    $reply
                        ->setEntityId(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)
                        ->setSource(self::TYPE)
                        ->setSourceId($comment['official_answer']['id'])
                        ->setParentId($reviewId)
                        ->setCreatedAt($comment['updateTimestamp'] ?? $this->dateTime->timestamp())
                        ->setEntityPkValue($productId)
                        ->setCustomerId($customerId)
                        ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)
                        ->setTitle('_robot_')
                        ->setDetail($msgReply)
                        ->setNickname($nickReply)
                        ->setStoreId($this->_storeId)
                        ->setStores($this->_stores)
                        ->save();

                    $this->_logger->info(
                        'Yandex: save reply id: ' . $comment['official_answer']['id']
                        . ' on parent comment id: ' . $reviewId
                    );
                    $ret++;
                }
                break;
            }
        }

        return $ret;
    }

    /**
     * Get work Yandex Url
     *
     * @return string
     */
    public function getUrl() : string
    {
        return sprintf('https://api.content.market.yandex.ru/v2/shops/%s/opinions', $this->getConfigValue('shop_id'));
    }

    /**
     * GET parameters
     *
     * @return array
     */
    public function getParams() : array
    {
        return [
            'count'        => 30,
            'sort'         => 'date',
            'how'          => 'desc',
            'max_comments' => 1
        ];
    }
}
