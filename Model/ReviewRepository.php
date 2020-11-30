<?php

namespace Emagento\Comments\Model;

use Emagento\Comments\Api\Data;
use Emagento\Comments\Api\ReviewRepositoryInterface;
use Emagento\Comments\Model\ResourceModel\Review as ResourceReview;
use Emagento\Comments\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

class ReviewRepository implements ReviewRepositoryInterface
{
    /**
     * @var ResourceReview
     */
    protected $resource;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var ReviewCollectionFactory
     */
    protected $reviewCollectionFactory;

    /**
     * @var Data\ReviewSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var \Emagento\Comments\Api\Data\ReviewInterfaceFactory
     */
    //protected $dataReviewFactory;

    /**
     * @param ResourceView $resource
     * @param ReviewFactory $reviewFactory
     * @param ReviewCollectionFactory $reviewCollectionFactory
     * @param Data\ReviewSearchResultsInterfaceFactory $searchResultsFactory
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResourceReview $resource,
        ReviewFactory $reviewFactory,
        ReviewCollectionFactory $reviewCollectionFactory,
        Data\ReviewSearchResultsInterfaceFactory $searchResultsFactory,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->resource = $resource;
        $this->reviewFactory = $reviewFactory;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor ?: $this->getCollectionProcessor();
    }

    /**
     * Save Review data
     *
     * @param \Emagento\Comments\Api\Data\ReviewInterface|Review $review
     * @return Review
     * @throws CouldNotSaveException
     */
    public function save(\Emagento\Comments\Api\Data\ReviewInterface $review)
    {
        if ($review->getStoreId() === null) {
            $storeId = $this->storeManager->getStore()->getId();
            $review->setStoreId($storeId);
        }
        try {
            $this->validateLayoutUpdate($review);
            $this->resource->save($review);
            //$this->identityMap->add($review);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(
                __('Could not save the review: %1', $exception->getMessage()),
                $exception
            );
        }

        return $review;
    }

    /**
     * Load Review data by given Review Identity
     *
     * @param int $reviewId
     * @return Review
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($reviewId)
    {
        $review = $this->reviewFactory->create();
        $review->load($reviewId);
        if (!$review->getId()) {
            throw new NoSuchEntityException(__('The Review with the "%1" ID doesn\'t exist.', $reviewId));
        }
        //$this->identityMap->add($review);

        return $review;
    }

    /**
     * Load Review data collection by given search criteria
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @param \Magento\Framework\Api\SearchCriteriaInterface $criteria
     * @return \Emagento\Comments\Api\Data\ReviewSearchResultsInterface
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        /** @var \Emagento\Comments\Model\ResourceModel\Review\Collection $collection */
        $collection = $this->reviewCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        /** @var Data\ReviewSearchResultsInterface $searchResults */
        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);
        $searchResults->setItems($collection->getItems());
        $searchResults->setTotalCount($collection->getSize());

        return $searchResults;
    }

    /**
     * Delete Review
     *
     * @param \Emagento\Comments\Api\Data\ReviewInterface $review
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(\Emagento\Comments\Api\Data\ReviewInterface $review)
    {
        try {
            $this->resource->delete($review);
            //$this->identityMap->remove($review->getId());
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(
                __('Could not delete the review: %1', $exception->getMessage())
            );
        }

        return true;
    }

    /**
     * Delete Review by given Review Identity
     *
     * @param int $reviewId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById($reviewId)
    {
        return $this->delete($this->getById($reviewId));
    }

    /**
     * Retrieve collection processor
     *
     * @return CollectionProcessorInterface
     */
    private function getCollectionProcessor()
    {
        if (!$this->collectionProcessor) {
            $this->collectionProcessor = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Emagento\Comments\Model\Api\SearchCriteria\ReviewCollectionProcessor::class
            );
        }
        return $this->collectionProcessor;
    }
}
