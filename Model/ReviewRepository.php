<?php

namespace Emagento\Comments\Model;

use Emagento\Comments\Api\ReviewRepositoryInterface;
use Emagento\Comments\Model\ReviewFactory;
use Emagento\Comments\Model\ResourceModel\Review as ReviewResourceModel;
use Emagento\Comments\Model\ResourceModel\Review\CollectionFactory;
use Magento\Framework\Api\SearchResultsFactory;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\DB\Adapter\ConnectionException;
use Magento\Framework\Exception\ValidatorException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;

/**
 * Class ReviewRepository model
 */
class ReviewRepository implements ReviewRepositoryInterface
{
    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var ReviewResourceModel
     */
    private $reviewResourceModel;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var SearchResultsFactory
     */
    private $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * ReviewRepository constructor.
     * @param ReviewFactory $reviewFactory
     * @param ReviewResourceModel $reviewResourceModel
     * @param CollectionFactory $collectionFactory
     * @param SearchResultsFactory $searchResultsFactory
     * @param CollectionProcessorInterface|null $collectionProcessor
     */
    public function __construct(
        ReviewFactory $reviewFactory,
        ReviewResourceModel $reviewResourceModel,
        CollectionFactory $collectionFactory,
        SearchResultsFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor = null
    )
    {
        $this->reviewFactory = $reviewFactory;
        $this->reviewResourceModel = $reviewResourceModel;
        $this->collectionFactory = $collectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor ?: \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface::class
        );
    }

    /**
     * @return ReviewFactory
     */
    public function getFactory()
    {
        return $this->reviewFactory;
    }

    /**
     * @param Review $review
     * @return bool|mixed
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function save(Review $review)
    {
        if ($review) {
            try {
                $this->reviewResourceModel->save($review);
            } catch (ConnectionException $exception) {
                throw new CouldNotSaveException(
                    __('Database connection error'),
                    $exception,
                    $exception->getCode()
                );
            } catch (CouldNotSaveException $e) {
                throw new CouldNotSaveException(__('Unable to save item'), $e);
            } catch (ValidatorException $e) {
                throw new CouldNotSaveException(__($e->getMessage()));
            }
            return $this->getById($review->getId());
        }
        return false;
    }

    /**
     * @param $reviewId
     * @param bool $editMode
     * @param null $storeId
     * @param bool $forceReload
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getById($reviewId, $editMode = false, $storeId = null, $forceReload = false)
    {
        $review = $this->reviewFactory->create();
        $this->reviewResourceModel->load($post, $reviewId);
        if (!$review->getId()) {
            throw new NoSuchEntityException(__('Requested item doesn\'t exist'));
        }
        return $review;
    }

    /**
     * @param Review $review
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws StateException
     */
    public function delete(Review $review)
    {
        try {
            $this->reviewResourceModel->delete($review);
        } catch (ValidatorException $e) {
            throw new CouldNotDeleteException(__($e->getMessage()));
        } catch (\Exception $e) {
            throw new StateException(
                __('Unable to remove item')
            );
        }
        return true;
    }

    /**
     * @param $postId
     * @return bool|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function deleteById($reviewId)
    {
        return $this->delete($this->getById($reviewId));
    }

    /**
     * {@inheritdoc}
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        /** @var \Emagento\Comments\Model\ResourceModel\Review\Collection $collection */
        $collection = $this->collectionFactory->create();

        $this->collectionProcessor->process($searchCriteria, $collection);

        /** @var \Magento\Framework\Api\searchResultsInterface $searchResult */
        $searchResult = $this->searchResultsFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());
        $searchResult->setItems($collection->getData());

        return $searchResult;
    }
}
