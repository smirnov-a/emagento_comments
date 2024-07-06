<?php

namespace Emagento\Comments\Model;

use Magento\Framework\Api\DataObjectHelper;
use Emagento\Comments\Api\ReviewInterface;
use Emagento\Comments\Api\ReviewInterfaceFactory;
use Emagento\Comments\Api\RatingRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Escaper;
use Magento\Framework\UrlInterface;
use Magento\Review\Model\Review as MagentoReview;
use Magento\Store\Model\Store;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory as VoteCollectionFactory;
use Emagento\Comments\Model\DataProvider\Rating as RatingDataProvider;
use Magento\Framework\App\CacheInterface;
use Emagento\Comments\Model\Cache\Type\CacheType;

class Review extends MagentoReview implements ReviewInterface
{
    /** @var CustomerRepositoryInterface */
    private CustomerRepositoryInterface $customerRepository;
    /** @var UrlInterface */
    private UrlInterface $urlModel;
    /** @var Escaper */
    private Escaper $escaper;
    /** @var VoteCollectionFactory */
    private VoteCollectionFactory $voteCollectionFactory;
    /**  @var ReviewInterfaceFactory */
    private ReviewInterfaceFactory $entityDataFactory;
    /**  @var DataObjectHelper */
    private DataObjectHelper $dataObjectHelper;
    /** @var RatingRepositoryInterface */
    private RatingRepositoryInterface $ratingRepository;
    /**  @var RatingDataProvider */
    private RatingDataProvider $ratingDataProvider;
    /** @var CacheInterface */
    private CacheInterface $cacheManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $productFactory
     * @param \Magento\Review\Model\ResourceModel\Review\Status\CollectionFactory $statusFactory
     * @param \Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory $summaryFactory
     * @param MagentoReview\SummaryFactory $summaryModFactory
     * @param MagentoReview\Summary $reviewSummary
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param VoteCollectionFactory $voteCollectionFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param UrlInterface $urlModel
     * @param Escaper $escaper
     * @param DataObjectHelper $dataObjectHelper
     * @param ReviewInterfaceFactory $entityDataFactory
     * @param RatingRepositoryInterface $ratingRepository
     * @param RatingDataProvider $ratingDataProvider
     * @param CacheInterface $cacheManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $productFactory,
        \Magento\Review\Model\ResourceModel\Review\Status\CollectionFactory $statusFactory,
        \Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory $summaryFactory,
        \Magento\Review\Model\Review\SummaryFactory $summaryModFactory,
        \Magento\Review\Model\Review\Summary $reviewSummary,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        VoteCollectionFactory $voteCollectionFactory,
        CustomerRepositoryInterface $customerRepository,
        UrlInterface $urlModel,
        Escaper $escaper,
        DataObjectHelper $dataObjectHelper,
        ReviewInterfaceFactory $entityDataFactory,
        RatingRepositoryInterface $ratingRepository,
        RatingDataProvider $ratingDataProvider,
        CacheInterface $cacheManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $productFactory,
            $statusFactory,
            $summaryFactory,
            $summaryModFactory,
            $reviewSummary,
            $storeManager,
            $urlModel,
            $resource,
            $resourceCollection,
            $data
        );
        $this->customerRepository = $customerRepository;
        $this->urlModel = $urlModel;
        $this->escaper = $escaper;
        $this->voteCollectionFactory = $voteCollectionFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->entityDataFactory = $entityDataFactory;
        $this->ratingRepository = $ratingRepository;
        $this->ratingDataProvider = $ratingDataProvider;
        $this->cacheManager = $cacheManager;
    }

    /**
     * Set Review ID
     *
     * @param int|null $value
     * @return ReviewInterface
     */
    public function setReviewId(?int $value): ReviewInterface
    {
        return $this->setData('review_id', $value);
    }

    /**
     * Set Review ID
     *
     * @return int|null
     */
    public function getReviewId(): ?int
    {
        return $this->getData(self::REVIEW_ID);
    }

    /**
     * Get Rating Votes
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb|null
     */
    public function getRatingVotes()
    {
        return $this->getData(self::RATING_VOTES);
    }

    /**
     * Get Path
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->getData(self::PATH);
    }

    /**
     * Set Path
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setPath(?string $value): ReviewInterface
    {
        return $this->setData(self::PATH, $value);
    }

    /**
     * Get Source
     *
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->getData(self::SOURCE);
    }

    /**
     * Set Source
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setSource(?string $value): ReviewInterface
    {
        return $this->setData(self::SOURCE, $value);
    }

    /**
     * Get Rating
     *
     * @return string|null
     */
    public function getRating(): ?string
    {
        return $this->getData(self::RATING);
    }

    /**
     * Set Rating
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setRating(?string $value): ReviewInterface
    {
        return $this->setData(self::RATING, $value);
    }

    /**
     * Get Parent Review ID
     *
     * @return string|null
     */
    public function getParentId(): ?string
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * Set Parent Review ID
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setParentId(?string $value): ReviewInterface
    {
        return $this->setData(self::PARENT_ID, $value);
    }

    /**
     * Get Source ID
     *
     * @return string|null
     */
    public function getSourceId(): ?string
    {
        return $this->getData(self::SOURCE_ID);
    }

    /**
     * Set Source ID
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setSourceId(?string $value): ReviewInterface
    {
        return $this->setData(self::SOURCE_ID, $value);
    }

    /**
     * Get Level
     *
     * @return string|null
     */
    public function getLevel(): ?string
    {
        return $this->getData(self::LEVEL);
    }

    /**
     * Set Level
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setLevel(?string $value): ReviewInterface
    {
        return $this->setData(self::LEVEL, $value);
    }

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * Set Created At
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setCreatedAt(?string $value): ReviewInterface
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * Set Updated At
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setUpdatedAt(?string $value): ReviewInterface
    {
        return $this->setData(self::UPDATED_AT, $value);
    }

    /**
     * Set Entity Primary Key Value
     *
     * @param int|null $value
     * @return ReviewInterface
     */
    public function setEntityPkValue(?int $value): ReviewInterface
    {
        return $this->setData('entity_pk_value', $value);
    }

    /**
     * Set Customer ID
     *
     * @param int|null $value
     * @return ReviewInterface
     */
    public function setCustomerId(?int $value): ReviewInterface
    {
        return $this->setData('customer_id', $value);
    }

    /**
     * Set Status ID
     *
     * @param int $value
     * @return ReviewInterface
     */
    public function setStatusId(int $value): ReviewInterface
    {
        return $this->setData(self::STATUS_ID, $value);
    }

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * Set Title
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setTitle(?string $value): ReviewInterface
    {
        $this->setData(self::TITLE, $value);
        return $this;
    }

    /**
     * Get Detail
     *
     * @return string|null
     */
    public function getDetail(): ?string
    {
        return $this->getData(self::DETAIL);
    }

    /**
     * Set Detail
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setDetail(?string $value): ReviewInterface
    {
        return $this->setData(self::DETAIL, $value);
    }

    /**
     * Get Nickname
     *
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->getData(self::NICKNAME);
    }

    /**
     * Set Nickname
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setNickname(?string $value): ReviewInterface
    {
        return $this->setData(self::NICKNAME, $value);
    }

    /**
     * Set Store ID
     *
     * @param int|null $value
     * @return ReviewInterface
     */
    public function setStoreId(?int $value): ReviewInterface
    {
        return $this->setData('store_id', $value);
    }

    /**
     * Set Stores
     *
     * @param array|null $value
     * @return ReviewInterface
     */
    public function setStores(?array $value): ReviewInterface
    {
        return $this->setData('stores', $value);
    }

    /**
     * Get Author Info
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getAuthorInfo(): string
    {
        try {
            $customer = $this->customerRepository->getById($this->getCustomerId());
            $customerText = __(
                '<a href="%1" onclick="this.target=\'blank\'">%2 %3</a> <a href="mailto:%4">(%4)</a>',
                $this->urlModel->getUrl('customer/index/edit', ['id' => $customer->getId(), 'active_tab' => 'review']),
                $this->escaper->escapeHtml($customer->getFirstname()),
                $this->escaper->escapeHtml($customer->getLastname()),
                $this->escaper->escapeHtml($customer->getEmail())
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customerText = ($this->getStoreId() == Store::DEFAULT_STORE_ID)
                ? __('Administrator') : __('Guest');
        }
        return $customerText;
    }

    /**
     * Get Ratings Data
     *
     * @return array
     */
    public function getRatingsData(): array
    {
        return $this->ratingDataProvider->getRatingsData($this->getReviewId());
    }

    /**
     * Get DataModel
     *
     * @return ReviewInterface
     */
    public function getDataModel(): ReviewInterface
    {
        $entityData = $this->getData();

        $entityDataObject = $this->entityDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $entityDataObject,
            $entityData,
            ReviewInterface::class
        );

        return $entityDataObject;
    }

    /**
     * After Save
     *
     * @return ReviewInterface
     */
    public function afterSave(): ReviewInterface
    {
        parent::afterSave();
        if ($this->hasDataChanges()) {
            $this->cacheManager->clean([CacheType::CACHE_TAG]);
        }

        return $this;
    }
}
