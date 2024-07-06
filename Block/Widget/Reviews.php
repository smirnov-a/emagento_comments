<?php

namespace Emagento\Comments\Block\Widget;

use Emagento\Comments\Api\Data\Review\ReviewInterface;
use Emagento\Comments\Helper\Constants;
use Emagento\Comments\Helper\Data as DataHelper;
use Emagento\Comments\Model\DataProvider\Review as ReviewDataProvider;
use Emagento\Comments\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Review\Model\Rating\Option\VoteFactory;
use Magento\Widget\Block\BlockInterface;

class Reviews extends Template implements BlockInterface
{
    private const WIDGET_TEMPLATE = 'Emagento_Comments::widget/reviews.phtml';
    private const MODE_REVIEW = 'review';
    private const MODE_REPLY = 'reply';

    /** @var string */
    protected $_template = self::WIDGET_TEMPLATE;
    /** @var ReviewCollectionFactory */
    protected ReviewCollectionFactory $reviewCollectionFactory;
    /** @var DataHelper */
    private DataHelper $dataHelper;
    /** @var VoteFactory */
    protected VoteFactory $voteFactory;
    /** @var ReviewDataProvider */
    private ReviewDataProvider $reviewDataProvider;

    /**
     * @param Template\Context $context
     * @param ReviewCollectionFactory $reviewCollectionFactory
     * @param DataHelper $dataHelper
     * @param VoteFactory $voteFactory
     * @param ReviewDataProvider $reviewDataProvider
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        ReviewCollectionFactory $reviewCollectionFactory,
        DataHelper $dataHelper,
        VoteFactory $voteFactory,
        ReviewDataProvider $reviewDataProvider,
        array $data = [],
    ) {
        parent::__construct($context, $data);
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->dataHelper = $dataHelper;
        $this->voteFactory = $voteFactory;
        $this->reviewDataProvider = $reviewDataProvider;
    }

    /**
     * Get Reviews
     *
     * @return \Emagento\Comments\Model\Data\Review\ReviewResults
     */
    public function getReviews()
    {
        try {
            return $this->reviewDataProvider->getReviews(1, $this->getReviewLimit());
        } catch (\Throwable $e) { // phpcs:ignore
        }
    }

    /**
     * Process Data
     *
     * @param array $data
     * @return void
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function processData(array &$data): void
    {
        foreach ($data as &$item) {
            $votes = $this->voteFactory->create()->getResourceCollection()
                ->setReviewFilter($item['review_id'])
                ->setStoreFilter($this->_storeManager->getStore()->getId())
                ->addRatingInfo($this->_storeManager->getStore()->getId())
                ->toArray();

            $item['rating_votes'] = $votes['items'];
        }
    }

    /**
     * Get Logo Image Path
     *
     * @param ReviewInterface $item
     * @param string $mode
     * @return string
     */
    public function getLogoImage(ReviewInterface $item, string $mode = self::MODE_REVIEW): string
    {
        $images = $this->dataHelper->getLogoImagesArray();
        if ($mode == self::MODE_REPLY) {
            return $images['local'];
        }

        switch ($item->getSource()) {
            case Constants::TYPE_FLAMP:
            case Constants::TYPE_YANDEX:
                return $images[$item->getSource()];
            default:
                return $images['local'];
        }
    }

    /**
     * Get Review Limit
     *
     * @return int
     */
    public function getReviewLimit(): int
    {
        return $this->getData('limit') ?: Constants::LIMIT;
    }
}
