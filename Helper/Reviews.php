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
     * Возвращает колекцию с комментариями к магазину
     * @param int $page текущая страница
     * @param int $limit кол-во комментариев
     *
     * @return Emagento\Comments\Model\ResourceModel\Review\Collection
     */
    public function getReviewList($page = 1, $limit = 5)
    {
        //$this->logger->info(__METHOD__.'; count: '.$count);
        $collection = $this->_reviewCollectionFactory->create()
            ->addReviewReplyOneLevel($page, $limit);
        // прицепить рейтинги (там внутри цикл и запрос для каждого элемента коллекции :(
        // но в родном блоке с отзывами так же
        // решается кэшированием
        $collection->load()
            ->addRateVotes();
        // коллекцию рейтингов привести к массиву
        foreach ($collection as $item) {
            $item->setRatingVotes($item->getRatingVotes()->toArray());
        }

        return $collection;
    }
}
