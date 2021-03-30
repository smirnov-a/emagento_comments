<?php
/**
 *
 */
namespace Emagento\Comments\Model;

use Emagento\Comments\Api\ReviewManagementInterface;

class ReviewManagement extends AbstractManagement implements ReviewManagementInterface
{
    /**
     * @var \Emagento\Comments\Model\ResourceModel\Review\CollectionFactory
     */
    protected $_itemCollectionFactory;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory
     */
    protected $_optionFactory;

    /**
     * Initialize dependencies.
     *
     * @param \Emagento\Comments\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $optionFactory
     */
    public function __construct(
        \Emagento\Comments\Model\ResourceModel\Review\CollectionFactory $reviewCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $optionFactory
    ) {
        $this->_itemCollectionFactory = $reviewCollectionFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_optionFactory = $optionFactory;
    }

    /**
     * @return string
     */
    public function getRaings()
    {
        $response = [
            'rating_id' => null,
            'options' => [],
        ];
        /* нужно вернуть json в таком виде
        {code: 1, label: 'очень плохо'},
        {code: 2, label: 'плохо'},
        {code: 3, label: 'средне'},
        {code: 4, label: 'хорошо'},
        {code: 5, label: 'отлично'}
        */
        $values = [
            1 => 'очень плохо',
            2 => 'плохо',
            3 => 'средне',
            4 => 'хорошо',
            5 => 'отлично',
        ];
        // взять код рейтинга для магазина из конфига (6)
        $ratingId = $this->_scopeConfig->getValue(
            'local_comments/settings/rating_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $response['rating_id'] = $ratingId;
        // и опции для него из rating_options
        /** @var \Magento\Review\Model\ResourceModel\Rating\Option\Collection $collection */
        $collection = $this->_optionFactory->create();
        $collection
            ->addRatingFilter($ratingId)
            ->setPositionOrder()
            ->load();
        //echo $collection->getSelect(); exit;
        foreach ($collection as $option) {
            $response['options'][] = [
                'code' => $option->getId(),
                'label' => $values[$option->getValue()] ?? '__',
                'value' => $option->getValue(),
            ];
        }
        //var_dump($response); exit;
        return json_encode($response);
    }

    /**
     * Retrieve list of post by page type, term, store, etc
     *
     * @param  int $storeId
     * @param  int $page
     * @param  int $limit
     * @return string
     */
    public function getList($storeId, $page, $limit)
    {
        try {
            $collection = $this->_itemCollectionFactory->create()
                ->addStoreFilter($storeId)
                ->addReviewReplyOneLevel($page, $limit);
            $collection->load()
                ->addRateVotes();
            // коллекцию рейтингов привести к массиву
            foreach ($collection as $item) {
                $item->setRatingVotes($item->getRatingVotes()->toArray());
            }
            //echo $collection->getSelect(); exit;

            $reviews = [];
            foreach ($collection as $item) {
                $reviews[] = $this->getDynamicData($item);
            }

            $result = [
                'items' => $reviews,
                'totalRecords' => $collection->getSize(),
                'currentPage' => $collection->getCurPage(),
                'lastPage' => $collection->getLastPageNumber(),
            ];

            return json_encode($result);
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
