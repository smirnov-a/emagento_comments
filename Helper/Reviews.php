<?php

namespace Local\Comments\Helper;

class Reviews extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Local\Comments\Model\ResourceModel\Review\CollectionFactory
     */
    protected $_reviewCollectionFactory;

    /**
     * Reviews constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Local\Comments\Model\ResourceModel\Review\CollectionFactory $reviewFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Local\Comments\Model\ResourceModel\Review\CollectionFactory $reviewFactory
    ) {
        $this->_reviewCollectionFactory = $reviewFactory;
        parent::__construct($context);
    }

    /**
     * Возвращает колекцию с комментариями к магазину
     *
     * @return Local\Comments\Model\ResourceModel\Review\Collection
     */
    public function getReviewList()
    {
        $collection = $this->_reviewCollectionFactory->create()
            ->addReviewReplyOneLevel();
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
