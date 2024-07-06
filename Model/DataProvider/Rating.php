<?php

namespace Emagento\Comments\Model\DataProvider;

use Emagento\Comments\Api\Data\Rating\RatingInterface;
use Emagento\Comments\Api\Data\Rating\RatingInterfaceFactory;
use Emagento\Comments\Model\ResourceModel\Rating\CollectionFactory as RatingCollectionFactory;
use Emagento\Comments\Api\Data\Rating\OptionInterfaceFactory;
use Emagento\Comments\Helper\Data as Helper;
use Magento\Review\Model\Rating as ReviewRating;
use Emagento\Comments\Api\RatingRepositoryInterface;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory as VoteCollectionFactory;
use Emagento\Comments\Helper\Constants;

class Rating
{
    /** @var RatingInterfaceFactory */
    private RatingInterfaceFactory $ratingFactory;
    /** @var RatingCollectionFactory */
    private RatingCollectionFactory $ratingCollectionFactory;
    /** @var Helper */
    private Helper $helper;
    /** @var OptionInterfaceFactory */
    private OptionInterfaceFactory $optionFactory;
    /** @var RatingRepositoryInterface */
    private RatingRepositoryInterface $ratingRepository;
    /** @var VoteCollectionFactory */
    private VoteCollectionFactory $voteCollectionFactory;

    /**
     * @param RatingInterfaceFactory $ratingFactory
     * @param RatingCollectionFactory $ratingCollectionFactory
     * @param OptionInterfaceFactory $optionFactory
     * @param RatingRepositoryInterface $ratingRepository
     * @param VoteCollectionFactory $voteCollectionFactory
     * @param Helper $helper
     */
    public function __construct(
        RatingInterfaceFactory $ratingFactory,
        RatingCollectionFactory $ratingCollectionFactory,
        OptionInterfaceFactory $optionFactory,
        RatingRepositoryInterface $ratingRepository,
        VoteCollectionFactory $voteCollectionFactory,
        Helper $helper
    ) {
        $this->ratingFactory = $ratingFactory;
        $this->ratingCollectionFactory = $ratingCollectionFactory;
        $this->optionFactory = $optionFactory;
        $this->ratingRepository = $ratingRepository;
        $this->voteCollectionFactory = $voteCollectionFactory;
        $this->helper = $helper;
    }

    /**
     * Get Ratings
     *
     * @return RatingInterface[]
     */
    public function getRatings(): array
    {
        $result = [];

        $collection = $this->ratingCollectionFactory->create();
        $collection->setActiveFilter()
            ->addEntityFilter($this->helper->getStoreReviewEntityId())
            ->load()
            ->addOptionToItems();
        foreach ($collection as $rating) {
            $result[] = $this->prepareResult($rating);
        }
        return $result;
    }

    /**
     * Prepare Rating Element
     *
     * @param RatingInterface|ReviewRating $model
     * @return RatingInterface
     */
    private function prepareResult($model): RatingInterface
    {
        $rating = $this->ratingFactory->create();
        $rating->setRatingId($model->getRatingId())
            ->setEntityId($model->getEntityId())
            ->setRatingCode($model->getRatingCode())
            ->setOptions($this->getOptions($model))
        ;
        return $rating;
    }

    /**
     * Get Option Array
     *
     * @param RatingInterface|ReviewRating $model
     * @return \Emagento\Comments\Api\Data\Rating\OptionInterface[]
     */
    private function getOptions($model): array
    {
        $result = [];
        foreach ($model->getOptions() as $item) {
            $option = $this->optionFactory->create();
            $option->setOptionId($item->getOptionId())
                ->setValue($item->getValue());
            $result[] = $option;
        }

        return $result;
    }

    /**
     * Get Ratings Data
     *
     * @param int $reviewId
     * @return array
     */
    public function getRatingsData(int $reviewId): array
    {
        $data = [];
        $ratingData = $this->ratingRepository->getRatingOptionsByCode(Constants::REVIEW_ENTITY_TYPE_BY_STORE);
        $ratingIds = array_unique(array_column($ratingData, 'rating_id'));
        foreach ($ratingIds as $ratingId) {
            $dataRating = array_filter($ratingData, function ($item) use ($ratingId) {
                return $item['rating_id'] == $ratingId;
            });
            $dataItem = [];
            foreach ($dataRating as $item) {
                $dataItem['rating_id'] = $item['rating_id'];
                $dataItem['rating_code'] = $item['rating_code'];
                $dataItem['option_id'] = null;
                $dataItem['value'] = null;
                $dataItem['options'][] = [
                    'option_id' => $item['option_id'],
                    'value'     => $item['value'],
                ];
            }
            $data[] = $dataItem;
        }

        $voteCollection = $this->voteCollectionFactory->create()
            ->setReviewFilter($reviewId)
            ->addRatingInfo()
            ->load();

        foreach ($voteCollection->getItems() as $item) {
            $ratingId = (int) $item['rating_id'];
            foreach ($data as &$dataItem) {
                if ($dataItem['rating_id'] != $ratingId) {
                    continue;
                }
                $dataItem['option_id'] = (int) $item['option_id'];
                $dataItem['value'] = (int) $item['value'];
            }
        }

        return $data;
    }
}
