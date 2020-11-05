<?php

namespace Local\Comments\Block\Widget;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class Reviews extends Template implements BlockInterface
{
    protected $_template = "widget/reviews.phtml";

    /**
     * @var \Local\Comments\Model\ResourceModel\Review\CollectionFactory
     */
    protected $_reviewCollectionFactory;

    /**
     * Reviews constructor.
     * @param Template\Context $context
     * @param \Local\Comments\Model\ResourceModel\Review\CollectionFactory $reviewFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Local\Comments\Model\ResourceModel\Review\CollectionFactory $reviewFactory,
        array $data = []
    ) {
        $this->_reviewCollectionFactory = $reviewFactory;
        parent::__construct($context, $data);
    }

    /**
     * Возвращает комментарии к магазину
     */
    public function getReviews()
    {
        return $this->_reviewCollectionFactory->create()
            ->addReviewReplyOneLevel();
    }
}
