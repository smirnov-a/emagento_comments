<?php

namespace Emagento\Comments\Model\Review\Remote\Provider;

use Emagento\Comments\Api\ReviewProviderInterface;
use Emagento\Comments\Model\Review;

class FlampProvider extends AbstractProvider implements ReviewProviderInterface
{
    public const REMOTE_TYPE = 'flamp';
    private const FLAMP_API_URL =  'https://api.reviews.2gis.com/2.0/branches/';
    private const LIMIT = 24;
    // phpcs:disable
    private const FIELDS = 'meta.providers,meta.branch_rating,meta.branch_reviews_count,meta.org_rating,meta.org_reviews_count';
    // phpcs:enable

    /**
     * Get Url of the Service
     *
     * @return string
     */
    protected function getUrl(): string
    {
        return self::FLAMP_API_URL
            . $this->helper->getConfigValue('account_id', self::REMOTE_TYPE)
            . '/reviews'
            . '?' . http_build_query($this->getParams())
        ;
    }

    /**
     * Get Parameters of Request
     *
     * @return array
     */
    protected function getParams(): array
    {
        return [
            'limit'         => self::LIMIT,
            'is_advertiser' => 'false',
            'fields'        => self::FIELDS,
        ];
    }

    /**
     * Check Errors
     *
     * @param mixed $response
     */
    protected function checkErrors($response): bool
    {
        return $response
            && isset($response['meta']['code'])
            && $response['meta']['code'] == 200
            && isset($response['reviews'])
        ;
    }

    /**
     * Process Comments
     *
     * @param array $comments
     * @return void
     */
    protected function processComments(array $comments): void
    {
        foreach ($comments['reviews'] as $comment) {
            if (empty($comment['text'])) {
                continue;
            }
            $this->processItem($comment);
        }
    }

    /**
     * Process Item
     *
     * @param array $comment
     * @return void
     */
    private function processItem(array $comment): void
    {
        $detail = $this->escaper->escapeHtml($this->filterManager->removeTags($comment['text']));
        $nickname = !empty($comment['user']['name'])
            ? $this->escaper->escapeHtml($this->filterManager->removeTags($comment['user']['name']))
            : self::ANONYMOUS;

        $isNew = false;
        $data = [];
        $review = $this->reviewRepository
            ->getByAttributes(['source' => self::REMOTE_TYPE, 'source_id' => $comment['id']]);
        if (!$review->getId()) {
            $isNew = true;
            $data = [
                'entity_id'  => $this->getStoreReviewEntityId(),
                'source'     => self::REMOTE_TYPE,
                'source_id'  => $comment['id'],
                'created_at' => $comment['date_created'] ?? $this->dateTime->timestamp(),
                'updated_at' => $comment['date_edited'] ?? null,
                'status_id'  => Review::STATUS_PENDING,
                'title'      => self::ROBOT_TITLE,
                'detail'     => $detail,
                'nickname'   => $nickname,
                'store_id'   => $this->storeId,
                'stores'     => $this->stores,
            ];
        } elseif (empty($comment['date_edited'])) {
            if ($this->dateTime->timestamp($comment['date_edited'])
                > $this->dateTime->timestamp($review->getUpdatedAt())
            ) {
                $data = [
                    'created_at' => $comment['date_created'],
                    'updated_at' => $comment['date_edited'],
                    'detail'     => $detail,
                    'nickname'   => $nickname,
                ];
            }
        }
        $this->saveReview($review, $data, $isNew);
        $this->processRating($review->getId(), $comment);
        $this->processReplyReview($review->getId(), $comment);
    }

    /**
     * Process Reply on Review
     *
     * @param int $reviewId
     * @param array $comment
     * @return void
     */
    private function processReplyReview(int $reviewId, array $comment): void
    {
        $detailReply = $this->escaper->escapeHtml(
            $this->filterManager->removeTags($comment['official_answer']['text'] ?? '')
        );
        if (!$reviewId || empty($comment['official_answer']) || !$detailReply) {
            return;
        }

        $nicknameReply = $this->escaper->escapeHtml(
            $this->filterManager->removeTags($comment['official_answer']['org_name'] ?? '')
        );

        $isNew = false;
        $data = [];
        $reviewReply = $this->reviewRepository
            ->getByAttributes(['source' => self::REMOTE_TYPE, 'source_id' => $comment['official_answer']['id'] ?? 0]);
        if (!$reviewReply->getId()) {
            $isNew = true;
            $data = [
                'entity_id'  => $this->getStoreReviewEntityId(),
                'source'     => self::REMOTE_TYPE,
                'source_id'  => $comment['official_answer']['id'],
                'parent_id'  => $reviewId,
                'created_at' => $comment['official_answer']['date_created'] ?? $this->dateTime->timestamp(),
                'updated_at' => $comment['official_answer']['date_edited'] ?? null,
                'status_id'  => Review::STATUS_PENDING,
                'title'      => self::ROBOT_TITLE,
                'detail'     => $detailReply,
                'nickname'   => $nicknameReply,
                'store_id'   => $this->storeId,
                'stores'     => $this->stores,
            ];
        } elseif (!empty($comment['official_answer']['date_edited'])) {
            if ($this->dateTime->timestamp($comment['official_answer']['date_edited'])
                > $this->dateTime->timestamp($reviewReply->getUpdatedAt())
            ) {
                $data = [
                    'created_at' => $comment['official_answer']['date_created'],
                    'updated_at' => $comment['official_answer']['date_edited'],
                    'detail'     => $detailReply,
                    'nickname'   => $nicknameReply,
                ];
            }
        }
        $this->saveReview($reviewReply, $data, $isNew, true, $reviewId);
    }

    /**
     * Process Rating
     *
     * @param int $reviewId
     * @param array $comment
     * @return void
     */
    private function processRating(int $reviewId, array $comment): void
    {
        if (empty($comment['rating'])) {
            return;
        }

        $ratingId = $this->getRatingId();
        $options = $this->getRatingOptions();
        $vote = $options[$ratingId][$comment['rating']] ?? null;
        if ($vote) {
            $this->ratingFactory->create()
                ->setRatingId($ratingId)
                ->setReviewId($reviewId)
                ->addOptionVote($vote, 0);
        }
    }
}
