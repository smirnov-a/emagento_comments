<?php

namespace Emagento\Comments\Model\DataProvider;

use Emagento\Comments\Api\Data\Review\RatingSummaryInterface;
use Emagento\Comments\Api\Data\Review\RatingSummaryInterfaceFactory;
use Emagento\Comments\Api\Data\Review\ReplyDataInterface;
use Emagento\Comments\Api\Data\Review\ReplyDataInterfaceFactory;
use Emagento\Comments\Api\Data\Review\ReviewInterface;
use Emagento\Comments\Api\Data\Review\ReviewInterfaceFactory;
use Emagento\Comments\Api\ReviewInterface as ReviewModelInterface;
use Emagento\Comments\Helper\Constants;
use Emagento\Comments\Helper\Data as DataHelper;
use Emagento\Comments\Model\Cache\Type\CacheType;
use Emagento\Comments\Api\Data\Review\ReviewResultsInterface;
use Emagento\Comments\Api\Data\Review\ReviewResultsInterfaceFactory;
use Emagento\Comments\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Framework\App\Cache\StateInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Review\Model\Review as MagentoReview;
use Magento\Store\Model\StoreManagerInterface;

class Review
{
    public const CACHE_KEY_PREFIX = 'EMAGENTO_REVIEW_LIST_';
    private const CACHE_LIFETIME = 86400;

    /** @var ReviewCollectionFactory */
    private ReviewCollectionFactory $reviewCollectionFactory;
    /** @var ReviewInterfaceFactory */
    private ReviewInterfaceFactory $reviewFactory;
    /** @var CacheInterface */
    private CacheInterface $cache;
    /** @var StateInterface */
    private StateInterface $state;
    /** @var StoreManagerInterface */
    private StoreManagerInterface $storeManager;
    /** @var bool|null */
    private ?bool $isCacheEnabled = null;
    /** @var RatingSummaryInterfaceFactory */
    private RatingSummaryInterfaceFactory $ratingSummaryFactory;
    /** @var ReplyDataInterfaceFactory */
    private ReplyDataInterfaceFactory $replyDataFactory;
    /** @var DataHelper */
    private DataHelper $dataHelper;
    /** @var ReviewResultsInterfaceFactory */
    private ReviewResultsInterfaceFactory $resultFactory;

    /**
     * @param ReviewCollectionFactory $reviewCollectionFactory
     * @param ReviewInterfaceFactory $reviewFactory
     * @param CacheInterface $cache
     * @param StateInterface $state
     * @param StoreManagerInterface $storeManager
     * @param RatingSummaryInterfaceFactory $ratingSummaryFactory
     * @param ReplyDataInterfaceFactory $replyDataFactory
     * @param DataHelper $dataHelper
     * @param ReviewResultsInterfaceFactory $resultFactory
     */
    public function __construct(
        ReviewCollectionFactory $reviewCollectionFactory,
        ReviewInterfaceFactory $reviewFactory,
        CacheInterface $cache,
        StateInterface $state,
        StoreManagerInterface $storeManager,
        RatingSummaryInterfaceFactory $ratingSummaryFactory,
        ReplyDataInterfaceFactory $replyDataFactory,
        DataHelper $dataHelper,
        ReviewResultsInterfaceFactory $resultFactory,
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->reviewFactory = $reviewFactory;
        $this->cache = $cache;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->ratingSummaryFactory = $ratingSummaryFactory;
        $this->replyDataFactory = $replyDataFactory;
        $this->dataHelper = $dataHelper;
        $this->resultFactory = $resultFactory;
    }

    /**
     * Get Reviews
     *
     * @param int $page
     * @param int $limit
     * @return ReviewResultsInterface
     * @throws NoSuchEntityException
     */
    public function getReviews(int $page = 1, int $limit = Constants::LIMIT)
    {
        $cacheKey = null;
        if ($this->isCacheEnabled()) {
            $cacheKey = $this->getCacheKey($page, $limit);
            if ($serializedData = $this->cache->load($cacheKey)) {
                // phpcs:ignore
                return unserialize($serializedData);
            }
        }

        $result = $this->resultFactory->create();
        $items = [];
        $collection = $this->reviewCollectionFactory->create()
            ->addStatusFilter(MagentoReview::STATUS_APPROVED)
            ->addReviewReplyOneLevel($page, $limit)
        ;
        $collection->load()
            ->addRateVotes();

        foreach ($collection as $review) {
            $items[] = $this->prepareResult($review);
        }

        $result->setItems($items)
            ->setTotalCount($collection->getSize());

        if ($cacheKey) {
            $this->cache->save(
                // phpcs:ignore
                serialize($result),
                $cacheKey,
                [
                    CacheType::CACHE_TAG,
                ],
                self::CACHE_LIFETIME
            );
        }

        return $result;
    }

    /**
     * Prepare Item Result
     *
     * @param ReviewModelInterface $model
     * @return ReviewInterface
     */
    private function prepareResult(ReviewModelInterface $model): ReviewInterface
    {
        $review = $this->reviewFactory->create();
        $review->setReviewId($model->getReviewId())
            ->setCreatedAt($model->getCreatedAt())
            ->setSource($model->getSource())
            ->setSourceId($model->getSourceId())
            ->setUpdatedAt($model->getUpdatedAt())
            ->setParentId($model->getParentId())
            ->setLevel($model->getLevel())
            ->setPath($model->getPath())
            ->setRating($model->getRating())
            ->setDetail($model->getDetail())
            ->setTitle($model->getTitle())
            ->setNickname($model->getNickname())
            ->setRatingSummary($this->getRatingSummary($model))
            ->setReplyData($this->getReplyData($model))
            ->setLogoImage($this->getLogoImage($model))
        ;
        return $review;
    }

    /**
     * Get Review Reply Data
     *
     * @param ReviewModelInterface $model
     * @return ReplyDataInterface
     */
    private function getReplyData(ReviewModelInterface $model): ReplyDataInterface
    {
        $replyData = $this->replyDataFactory->create();
        $replyData
            ->setReviewId($model->getData('r_review_id'))
            ->setCustomerId($model->getData('r_customer_id'))
            ->setDetail($model->getData('r_detail'))
            ->setDetailId($model->getData('r_detail_id'))
            ->setLevel($model->getData('r_level'))
            ->setNickname($model->getData('r_nickname'))
            ->setTitle($model->getData('r_title'))
            ->setCreatedAt($model->getData('r_created_at'))
            ->setLogoImage($this->getLogoImage())
        ;
        return $replyData;
    }

    /**
     * Get Logo Image Path
     *
     * @param ReviewModelInterface|null $model
     * @return string
     */
    private function getLogoImage(?ReviewModelInterface $model = null): string
    {
        $images = $this->dataHelper->getLogoImagesArray();
        if (!$model) {
            return $images['local'];
        }

        switch ($model->getSource()) {
            case Constants::TYPE_FLAMP:
            case Constants::TYPE_YANDEX:
                return $images[$model->getSource()];
            default:
                return $images['local'];
        }
    }

    /**
     * Get Rating Summary
     *
     * @param ReviewModelInterface $model
     * @return RatingSummaryInterface
     */
    private function getRatingSummary(ReviewModelInterface $model): RatingSummaryInterface
    {
        $count = 0;
        $value = 0;
        foreach ($model->getRatingVotes() as $ratingVote) {
            $value += $ratingVote->getValue();
            $count++;
        }
        $value = $count ? (int) ceil($value / $count) : 0;
        $ratingSummary = $this->ratingSummaryFactory->create();
        $ratingSummary
            ->setRatingId($model->getRatingVotes()->getFirstItem()->getRatingId())
            ->setDescription($this->getRatingDescription($value))
            ->setPercent($value * 20)
            ->setValue($value);

        return $ratingSummary;
    }

    /**
     * Get Rating Description
     *
     * @param int $value
     * @return string
     */
    private function getRatingDescription(int $value): string
    {
        $ratingValue = Constants::RATING_VALUES[$value] ?? '';
        return __($ratingValue);
    }

    /**
     * Is Cache Enabled
     *
     * @return bool
     */
    private function isCacheEnabled(): bool
    {
        if (null === $this->isCacheEnabled) {
            $this->isCacheEnabled = $this->state->isEnabled(CacheType::TYPE_IDENTIFIER);
        }

        return $this->isCacheEnabled;
    }

    /**
     * Get Cache Key
     *
     * @param int $page
     * @param int $limit
     * @return string
     * @throws NoSuchEntityException
     */
    private function getCacheKey(int $page, int $limit): string
    {
        $storeId = $this->storeManager->getStore()->getId();
        return self::CACHE_KEY_PREFIX . $storeId . '_' . $page . '_' . $limit;
    }
}
