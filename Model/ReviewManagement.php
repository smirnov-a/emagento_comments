<?php

namespace Emagento\Comments\Model;

use Emagento\Comments\Api\ReviewManagementInterface;
use Emagento\Comments\Model\ResourceModel\Review\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory as RatingOptionCollectionFactory;

class ReviewManagement extends AbstractManagement implements ReviewManagementInterface
{
    /**
     * @var CollectionFactory
     */
    protected $_itemCollectionFactory;
    /**
     * @var ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var RatingOptionCollectionFactory
     */
    protected $_optionFactory;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    /**
     * Initialize dependencies.
     *
     * @param CollectionFactory $reviewCollectionFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param RatingOptionCollectionFactory $optionFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        CollectionFactory $reviewCollectionFactory,
        ScopeConfigInterface $scopeConfig,
        RatingOptionCollectionFactory $optionFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->_itemCollectionFactory = $reviewCollectionFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_optionFactory = $optionFactory;
        $this->_jsonHelper = $jsonHelper;
    }

    /**
     * @return string
     */
    public function getRaings()
    {
        $response = [];
        $values = [
            1 => __('very bad'),
            2 => __('bad'),
            3 => __('medium'),
            4 => __('good'),
            5 => __('very good'),
        ];
        // get rating code from config (6)
        $ratingId = $this->_scopeConfig->getValue(
            'local_comments/settings/rating_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $response['rating_id'] = $ratingId;
        // get options for rating
        /** @var \Magento\Review\Model\ResourceModel\Rating\Option\Collection $collection */
        $collection = $this->_optionFactory->create();
        $collection
            ->addRatingFilter($ratingId)
            ->setPositionOrder();

        foreach ($collection as $option) {
            $response['options'][] = [
                'code'  => $option->getId(),
                'label' => $values[$option->getValue()] ?? '__',
                'value' => $option->getValue(),
            ];
        }

        return $this->_jsonHelper->jsonEncode($response);
    }

    /**
     * Retrieve list of post by page type, term, store, etc
     *
     * @param  int $storeId
     * @param  int $page
     * @param  int $limit
     * @return string|bool
     */
    public function getList($storeId, $page, $limit)
    {
        try {
            $collection = $this->_itemCollectionFactory->create()
                ->addStoreFilter($storeId)
                ->addReviewReplyOneLevel($page, $limit);
            $collection->load()
                ->addRateVotes();

            foreach ($collection as $item) {
                $item->setRatingVotes($item->getRatingVotes()->toArray());
            }

            $reviews = [];
            foreach ($collection as $item) {
                $reviews[] = $this->getDynamicData($item);
            }

            $response = [
                'items'        => $reviews,
                'totalRecords' => $collection->getSize(),
                'currentPage'  => $collection->getCurPage(),
                'lastPage'     => $collection->getLastPageNumber(),
            ];

            return $this->_jsonHelper->jsonEncode($response);

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param $item
     * @return array
     */
    protected function getDynamicData($item)
    {
        $data = $item->getData();

        $keys = [
            'created_at',
            'customer_id',
            'detail',
            'detail_id',
            'entity_id',
            'entity_pk_value',
            'level',
            'nickname',
            'parent_id',
            'path',
            'r_customer_id',
            'r_detail',
            'r_detail_id',
            'r_level',
            'r_nickname',
            'r_review_id',
            'r_title',
            'rating_votes',
            'review_id',
            'source',
            'source_id',
            'status_id',
            'title',
            'updated_at',
        ];

        foreach ($keys as $key) {
            $method = 'get' . str_replace(
                    '_',
                    '',
                    ucwords($key, '_')
                );
            $data[$key] = $item->$method();
        }

        return $data;
    }
}
