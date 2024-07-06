<?php

namespace Emagento\Comments\Ui\Component\Listing;

use Emagento\Comments\Model\ResourceModel\Review\Collection as ReviewCollection;
use Emagento\Comments\Model\ResourceModel\Review\CollectionFactory;
use Emagento\Comments\Model\ResourceModel\Review\Store as ReviewStoreResource;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\DB\Select;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory as RatingOptionVoteCollectionFactory;
use Magento\Review\Model\Review;
use Magento\Store\Model\StoreManagerInterface;

class DataProvider extends \Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider
{
    /** @var CollectionFactory */
    private CollectionFactory $collectionFactory;
    /** @var RatingOptionVoteCollectionFactory */
    private RatingOptionVoteCollectionFactory $ratingOptionVoteCollection;
    /** @var StoreManagerInterface */
    private StoreManagerInterface $storeManager;
    /** @var ReviewStoreResource */
    private ReviewStoreResource $reviewStoreResource;
    /** @var ReviewCollection */
    private ReviewCollection $collection;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param CollectionFactory $collectionFactory
     * @param RatingOptionVoteCollectionFactory $ratingOptionVoteCollection
     * @param StoreManagerInterface $storeManager
     * @param ReviewStoreResource $reviewStoreResource
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        CollectionFactory $collectionFactory,
        RatingOptionVoteCollectionFactory $ratingOptionVoteCollection,
        StoreManagerInterface $storeManager,
        ReviewStoreResource $reviewStoreResource,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->collectionFactory = $collectionFactory;
        $this->ratingOptionVoteCollection = $ratingOptionVoteCollection;
        $this->storeManager = $storeManager;
        $this->reviewStoreResource = $reviewStoreResource;

        $this->collection = $this->collectionFactory->create();
    }

    /**
     * Get Meta Array
     *
     * @return array
     */
    public function getMeta(): array
    {
        $meta = parent::getMeta();
        if ($this->storeManager->isSingleStoreMode()) {
            if (isset($meta['local_comments_grid']['children']['visible'])) {
                unset($meta['local_comments_grid']['children']['visible']);
            }
        }
        return $meta;
    }

    /**
     * Get Data Array
     *
     * @return array
     */
    public function getData(): array
    {
        $this->addOrderToCollection()
            ->addStoresAndRatingData();

        $data = [];
        foreach ($this->collection->getItems() as $review) {
            $data[] = $review->getData();
        }

        return [
            'totalRecords' => $this->collection->getSize(),
            'items'        => $data,
        ];
    }

    /**
     * Add Order To Collection
     *
     * @return $this
     */
    private function addOrderToCollection(): static
    {
        $sortOrders = $this->request->getParam('sorting', []);
        if (!$sortOrders) {
            return $this;
        }

        $field = $sortOrders['field'] ?? null;
        $direction = $sortOrders['direction'] ?? null;
        if ($field && $direction) {
            $this->collection->addOrder($field, $direction);
        }

        return $this;
    }

    /**
     * Add Stores And Rating Data
     *
     * @return void
     */
    private function addStoresAndRatingData()
    {
        $reviewIds = [];
        foreach ($this->collection->getItems() as $review) {
            $reviewIds[] = $review->getId();
        }
        if (count($reviewIds) === 0) {
            return;
        }

        $storeData = $this->getReviewStores($reviewIds);
        $ratingVotes = $this->getRatingOptionVotes($reviewIds);
        foreach ($this->collection->getItems() as $review) {
            $review->setStores($storeData[$review->getId()] ?? [])
                ->setRating($ratingVotes[$review->getId()] ?? null);
        }
    }

    /**
     * Get Review Stores
     *
     * @param array $reviewIds
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getReviewStores($reviewIds): array
    {
        $result = [];
        foreach ($this->reviewStoreResource->getReviewsStoreData($reviewIds) as $item) {
            $result[$item['review_id']][] = (int) $item['store_id'];
        }
        return $result;
    }

    /**
     * Get Rating Option Votes
     *
     * @param array $reviewIds
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getRatingOptionVotes(array $reviewIds): array
    {
        $ratingOptionVoteCollection = $this->ratingOptionVoteCollection->create();
        $ratingOptionVoteCollection->addFieldToFilter('main_table.review_id', ['in' => $reviewIds]);
        $sumCond = new \Zend_Db_Expr("SUM(main_table.{$ratingOptionVoteCollection->getConnection()->quoteIdentifier('percent')})"); // phpcs:ignore
        $countCond = new \Zend_Db_Expr('COUNT(*)');

        $select = $ratingOptionVoteCollection->getSelect()
            ->reset(Select::COLUMNS)
            ->columns($this->getRatingOptionVoteColumns(), 'main_table')
            ->columns([
                'sum'   => $sumCond,
                'count' => $countCond,
            ])
            ->join(
                ['review' =>$ratingOptionVoteCollection->getTable('review')],
                'review.review_id = main_table.review_id',
                []
            )
            ->where($ratingOptionVoteCollection->getConnection()->prepareSqlCondition('review.status_id', Review::STATUS_APPROVED)) // phpcs:ignore
            ->group($this->getRatingOptionVoteColumns())
        ;
        if (!$this->storeManager->isSingleStoreMode()) {
            $storeId = $this->storeManager->getStore()->getId();
            $select
                ->joinLeft(
                    ['rating_store' => $ratingOptionVoteCollection->getTable('rating_store')],
                    'rating_store.rating_id = main_table.rating_id',
                    []
                )
                ->where($ratingOptionVoteCollection->getConnection()->prepareSqlCondition('rating_store.store_id', $storeId)) // phpcs:ignore
            ;
        }

        $ratingItems = [];
        foreach ($ratingOptionVoteCollection->getItems() as $item) {
            $rating = $item->getCount() ? $item->getSum() / $item->getCount() : 0;
            if (!$rating) {
                continue;
            }
            $ratingItems[$item->getReviewId()] = ceil($rating / 100 * 5);
        }
        return $ratingItems;
    }

    /**
     * Get Rating Option Vote Columns
     *
     * @return string[]
     */
    private function getRatingOptionVoteColumns(): array
    {
        return [
            'vote_id', 'option_id', 'remote_ip', 'customer_id', 'entity_pk_value', 'rating_id', 'review_id',
        ];
    }
}
