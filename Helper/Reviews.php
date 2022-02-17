<?php

namespace Emagento\Comments\Helper;

class Reviews extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Emagento\Comments\Model\ResourceModel\Review\CollectionFactory
     */
    protected $_reviewCollectionFactory;

    private $logger;

    /**
     * Reviews constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Emagento\Comments\Model\ResourceModel\Review\CollectionFactory $reviewFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Emagento\Comments\Model\ResourceModel\Review\CollectionFactory $reviewFactory,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_reviewCollectionFactory = $reviewFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get store review collection
     *
     * @param int $page
     * @param int $limit
     *
     * @return Emagento\Comments\Model\ResourceModel\Review\Collection
     */
    public function getReviewList($page = 1, $limit = 5)
    {
        $collection = $this->_reviewCollectionFactory->create()
            ->addReviewReplyOneLevel($page, $limit);

        $collection->load()
            ->addRateVotes();

        foreach ($collection as $item) {
            $item->setRatingVotes($item->getRatingVotes()->toArray());
        }

        return $collection;
    }
}
