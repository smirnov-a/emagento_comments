<?php

namespace Emagento\Comments\Model\Review\Remote\Provider;

use Emagento\Comments\Api\ReviewProviderInterface;
use Emagento\Comments\Model\Review;

class YandexProvider extends AbstractProvider implements ReviewProviderInterface
{
    public const REMOTE_TYPE = 'yandex';
    private const YANDEX_API_URL = 'https://api.content.market.yandex.ru/v2/shops/';
    private const LIMIT = 30;
    private const ADVANTAGES = 'Advantages';
    private const LIMITATIONS = 'Limitations';
    private const COMPANY = 'Company';
    private const MAX_COMMENTS = 1;

    /**
     * Get Url of the Service
     *
     * @return string
     */
    protected function getUrl(): string
    {
        return self::YANDEX_API_URL
            . $this->helper->getConfigValue('account_id', self::REMOTE_TYPE)
            . '/opinions'
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
            'count'        => self::LIMIT,
            'sort'         => 'date',
            'how'          => 'desc',
            'max_comments' => self::MAX_COMMENTS,
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
            && empty($response['errors'])
            && !empty($response['shopOpinions']['opinion']);
    }

    /**
     * Process Comments
     *
     * @param array $comments
     * @return void
     */
    protected function processComments(array $comments): void
    {
        foreach ($comments['shopOpinions']['opinion'] as $comment) {
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
        $detail = $this->escaper->escapeHtml($comment['text'] ?? '');
        $pro = $this->escaper->escapeHtml($comment['pro'] ?? '');
        if ($pro) {
            $advantages = self::ADVANTAGES;
            $detail .= ($detail ? '<br/>' : '')
                . '<span>' . __($advantages) . ': </span>'
                . $pro;
        }
        $contra = $this->escaper->escapeHtml($comment['contra'] ?? '');
        if ($contra) {
            $limitations = self::LIMITATIONS;
            $detail .= ($detail ? '<br/>' : '')
                . '<span>' . __($limitations) . ': </span>'
                . $contra;
        }
        $detail = $this->filterManager->stripTags(
            $detail,
            ['allowableTags' => ['<br>', '<span>'], 'escape' => false]
        );

        $nickname = (!$comment['anonymous'] && !empty($comment['author']))
            ? $this->escaper->escapeHtml($this->filterManager->removeTags($comment['author']))
            : self::ANONYMOUS;

        $isNew = false;
        $data = [];
        $review = $this->reviewRepository
            ->getByAttributes(['source' => self::REMOTE_TYPE, 'source_id' => $comment['id']]);
        // only new reviews because Yandex doesn't permit editing
        if (!$review->getId()) {
            $isNew = true;
            $data = [
                'entity_id'  => $this->getStoreReviewEntityId(),
                'source'     => self::REMOTE_TYPE,
                'source_id'  => $comment['id'],
                'created_at' => $comment['date'] ?? $this->dateTime->timestamp(),
                'status_id'  => $this->helper->getDefaultReviewStatusId(),
                'title'      => self::ROBOT_TITLE,
                'detail'     => $detail,
                'nickname'   => $nickname,
                'store_id'   => $this->storeId,
                'stores'     => $this->stores,
            ];
        }
        $this->saveReview($review, $data, $isNew);
        $this->processRating($review->getId(), $comment);
        $this->processReplyReview($review->getId(), $comment);
    }

    /**
     * Process Reply Review
     *
     * @param int $reviewId
     * @param array $item
     * @return void
     */
    private function processReplyReview(int $reviewId, array $item): void
    {
        if (empty($item['comments'])) {
            return;
        }

        foreach ($item['comments'] as $comment) {
            $detailReply = $this->escaper->escapeHtml(
                $this->filterManager->stripTags(
                    $comment['body'] ?? '',
                    ['allowableTags' => ['<br>'], 'escape' => false]
                )
            );
            $isNew = false;
            $data = [];
            $company = self::COMPANY;
            $nicknameReply = !empty($comment['user']['name'])
                ? $this->escaper->escapeHtml($comment['user']['name'])
                : __($company);
            // try to load by Yandex id
            $reviewReply = $this->reviewRepository
                ->getByAttributes([
                    'source'    => self::REMOTE_TYPE,
                    'source_id' => $comment['official_answer']['id'] ?? 0
                ]);
            if (!$reviewReply->getId()) {
                $isNew = true;
                $data = [
                    'entity_id'  => $this->getStoreReviewEntityId(),
                    'source'     => self::REMOTE_TYPE,
                    'source_id'  => $comment['official_answer']['id'],
                    'parent_id'  => $reviewId,
                    'created_at' => $comment['updateTimestamp'] ?? $this->dateTime->timestamp(),
                    'status_id'  => $this->helper->getDefaultReviewStatusId(),
                    'title'      => self::ROBOT_TITLE,
                    'detail'     => $detailReply,
                    'nickname'   => $nicknameReply,
                    'store_id'   => $this->storeId,
                    'stores'     => $this->stores,
                ];
            }
            $this->saveReview($reviewReply, $data, $isNew, true, $reviewId);
            break;
        }
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
        // Yandex rating: 2, 1, 0, -1, -2
        if (empty($comment['grade'])) {
            return;
        }

        $ratingId = $this->getRatingId();
        $options = $this->getRatingOptions();
        $vote = $options[$ratingId][$comment['grade'] + 2] ?? null;
        if ($vote) {
            $this->ratingFactory->create()
                ->setRatingId($ratingId)
                ->setReviewId($reviewId)
                ->addOptionVote($vote, 0);
        }
    }
}
