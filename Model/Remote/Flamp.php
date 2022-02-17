<?php

namespace Emagento\Comments\Model\Remote;

/**
 * Work with Flamp
 */
class Flamp extends AbstractRemote
{
    const TYPE = 'flamp';

    /**
     * Work with flamp comments
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
        if (!$this->_workData || !isset($this->_workData['reviews'])) {
            if (isset($this->_workData['error_code']) && $this->_workData['message']) {
                $this->_logger->info('Error loading Flamp reviews: ' . $this->_workData['message']);
            }
            return 0;
        }
        $this->_logger->info('Flamp. Found ' . count($this->_workData['reviews']) . ' comments');

        foreach ($this->_workData['reviews'] as $item) {
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
     *
     * @param array $item
     * @return int
     * @throws \Exception
     */
    private function _processItem($item) : int
    {
        $ret = 0;
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

        $productId = 0;
        $customerId = null;
        if (!$review->getId()) {
            // have no review. add with "Store review" and "Approved"
            $review
                ->setEntityId(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)
                ->setSource(self::TYPE)
                ->setSourceId($item['id'])
                ->setCreatedAt($item['date_created'] ?? $this->dateTime->timestamp())
                ->setUpdatedAt($item['date_edited'] ?? null)
                ->setEntityPkValue($productId)
                ->setCustomerId($customerId)
                ->setStatusId(\Magento\Review\Model\Review::STATUS_APPROVED)
                ->setTitle('_robot_')
                ->setDetail($msgReview)
                ->setNickname($nick)
                ->setStoreId($this->_storeId)
                ->setStores($this->_stores)
                ->save();

            if (!empty($item['rating'])) {        // 1...5
                $_vote = $this->_ratingOptions[$this->_ratingId][$item['rating'] - 1] ?? null;
                if ($_vote) {
                    $this->_ratingFactory->create()
                        ->setRatingId($this->_ratingId)
                        ->setReviewId($review->getId())
                        ->addOptionVote($_vote, $productId);
                }
            }
            $review->aggregate();
            $this->_logger->info('Flamp: save comment id: ' . $item['id']);
            $ret++;
        } else {
            // have review
            // check if review changed (remote owner can modify review)
            if (!empty($item['date_edited'])) {
                // '2017-09-01T12:48:35.0+07:00' convert to time
                $time = $this->dateTime->timestamp($item['date_edited']);
                // если дата редактирования с нашей стороны пустая либо пришедшая дата больше нашей
                if (empty($review->getUpdatedAt())
                    || $time > $this->dateTime->timestamp($review->getUpdatedAt())
                ) {
                    $review
                        ->setCreatedAt($item['date_created'])
                        ->setUpdatedAt($item['date_edited'])
                        ->setDetail($msgReview)
                        ->setNickname($nick)
                        ->save();
                    $this->_logger->info('Flamp: update comment id: ' . $review->getId());
                    $ret++;
                }
            }
        }

        // check store answer on review
        if (!empty($item['official_answer'])) {
            // add with parent id
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
                            $item['official_answer']['date_created']  ?? $this->dateTime->timestamp()
                        )
                        ->setUpdatedAt($item['official_answer']['date_edited'] ?? null)
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
                        'Flamp: save reply id: ' . $item['official_answer']['id']
                        . ' on parent comment id: ' . $reviewId
                    );
                    $ret++;
                } else {
                    // check if date changed
                    if (!empty($item['official_answer']['date_edited'])) {
                        // '2017-09-01T12:48:35.0+07:00' convert to time
                        $time = $this->dateTime->timestamp($item['official_answer']['date_edited']);
                        if (empty($reply->getUpdatedAt())
                            || $time > $this->dateTime->timestamp($reply->getUpdatedAt())
                        ) {
                            $reply
                                ->setCreatedAt($item['official_answer']['date_created'])
                                ->setUpdatedAt($item['official_answer']['date_edited'])
                                ->setDetail($msgReply)
                                ->setNickname($nickReply)
                                ->save();
                            $this->_logger->info(
                                'Flamp: update reply comment id: ' . $reply->getId()
                            );
                            $ret++;
                        }
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Get Flamp Api url
     *
     * @return string
     */
    public function getUrl() : string
    {
        // https://api.reviews.2gis.com/2.0/branches/1267165676751243/reviews?limit=24&is_advertiser=false&fields=meta.providers,meta.branch_rating,meta.branch_reviews_count,meta.org_rating,meta.org_reviews_count
        return sprintf('https://api.reviews.2gis.com/2.0/branches/%s/reviews', $this->getConfigValue('flamp_id'));
    }

    /**
     * GET parameters
     *
     * @return array
     */
    public function getParams() : array
    {
        return [
            'limit'         => 24,
            'is_advertiser' => 'false',
            'fields'        => 'meta.providers,meta.branch_rating,meta.branch_reviews_count,meta.org_rating,meta.org_reviews_count',
        ];
    }
}
