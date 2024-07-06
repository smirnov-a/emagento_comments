<?php

namespace Emagento\Comments\Model;

use Emagento\Comments\Api\ReviewInterface;
use Emagento\Comments\Api\ReviewRepositoryInterface;
use Emagento\Comments\Model\ResourceModel\Review as ReviewResource;
use Emagento\Comments\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Framework\Api\SearchResults;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class ReviewRepository implements ReviewRepositoryInterface
{
    /** @var ReviewResource  */
    private ReviewResource $reviewResource;
    /** @var ReviewCollectionFactory  */
    private ReviewCollectionFactory $reviewCollectionFactory;
    /** @var SearchResultsFactory  */
    private SearchResultsFactory $searchResultsFactory;
    /** @var CollectionProcessorInterface|mixed  */
    private CollectionProcessorInterface $collectionProcessor;
    /** @var SearchCriteriaBuilder  */
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    /** @var ReviewFactory */
    private ReviewFactory $reviewFactory;

    /**
     * @param ReviewFactory $reviewFactory
     * @param ReviewResource $reviewResourceModel
     * @param ReviewCollectionFactory $reviewCollectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ReviewFactory                $reviewFactory,
        ReviewResource               $reviewResourceModel,
        ReviewCollectionFactory      $reviewCollectionFactory,
        SearchResultsFactory         $searchResultsFactory,
        SearchCriteriaBuilder        $searchCriteriaBuilder,
        CollectionProcessorInterface $collectionProcessor = null
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->reviewResource = $reviewResourceModel;
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionProcessor = $collectionProcessor ?:
            ObjectManager::getInstance()->get(CollectionProcessorInterface::class);
    }

    /**
     * Save Review
     *
     * @param ReviewInterface|Review $review
     * @return ReviewInterface
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function save($review): ReviewInterface
    {
        try {
            $this->reviewResource->save($review);
        } catch (ConnectionException $exception) {
            throw new CouldNotSaveException(
                __('Database connection error'),
                $exception,
                $exception->getCode()
            );
        } catch (CouldNotSaveException $e) {
            throw new CouldNotSaveException(__('Unable to save item'), $e);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        return $this->getById($review->getId());
    }

    /**
     * Get Review by ID
     *
     * @param int $reviewId
     * @return ReviewInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $reviewId): ReviewInterface
    {
        $review = $this->reviewFactory->create();
        $this->reviewResource->load($review, $reviewId);
        if (!$review->getId()) {
            throw new NoSuchEntityException(__('Requested item doesn\'t exist'));
        }
        return $review;
    }

    /**
     * Delete Review
     *
     * @param ReviewInterface|Review $review
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete($review): bool
    {
        try {
            $this->reviewResource->delete($review);
        } catch (ValidatorException $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to remove item'));
        }
        return true;
    }

    /**
     * Delete Review by ID
     *
     * @param int $reviewId
     * @return bool
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function deleteById(int $reviewId): bool
    {
        return $this->delete($this->getById($reviewId));
    }

    /**
     * Get List of Review
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return SearchResults
     */
    public function getList(SearchCriteriaInterface $searchCriteria): SearchResults
    {
        $collection = $this->reviewCollectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResult->setItems($items);
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * Get List of Review by Attributes filter
     *
     * @param array $attributes
     * @return ReviewInterface
     */
    public function getByAttributes(array $attributes)
    {
        foreach ($attributes as $attribute => $value) {
            $this->searchCriteriaBuilder->addFilter($attribute, $value);
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchResult = $this->getList($searchCriteria);

        return $searchResult->getTotalCount() ? $searchResult->getItems()[0] : $this->reviewFactory->create();
    }

    /**
     * Update Path and Level of Review
     *
     * @param int $reviewId
     * @param array $data
     * @return void
     */
    public function updatePathAndLevel(int $reviewId, array $data): void
    {
        $this->reviewResource->updatePathAndLevel($reviewId, $data);
    }

    /**
     * Get Review Factory
     *
     * @return ReviewFactory
     */
    public function getFactory(): ReviewFactory
    {
        return $this->reviewFactory;
    }
}
