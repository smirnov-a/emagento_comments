<?php

namespace Emagento\Comments\Ui\Component\DataProvider;

use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Emagento\Comments\Api\ReviewInterface;
use Emagento\Comments\Model\ReviewFactory;
use Emagento\Comments\Model\ReviewRepository;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Review\Model\RatingFactory;
use Magento\Store\Model\Store;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Escaper;

class ReviewDataProvider extends DataProvider
{
    /** @var ReviewRepository */
    private ReviewRepository $reviewRepository;
    /** @var ReviewFactory */
    private ReviewFactory $reviewFactory;
    /** @var CustomerRepositoryInterface */
    private CustomerRepositoryInterface $customerRepository;
    /** @var UrlInterface */
    private UrlInterface $urlBuilder;
    /** @var Escaper */
    private Escaper $escaper;
    /** @var RatingFactory */
    private RatingFactory $ratingFactory;
    /** @var array */
    private array $loadedData;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param ReviewRepository $reviewRepository
     * @param ReviewFactory $reviewFactory
     * @param CustomerRepositoryInterface $customerRepository
     * @param UrlInterface $urlBuilder
     * @param Escaper $escaper
     * @param RatingFactory $ratingFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        string $name,
        string $primaryFieldName,
        string $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        ReviewRepository $reviewRepository,
        ReviewFactory $reviewFactory,
        CustomerRepositoryInterface $customerRepository,
        UrlInterface $urlBuilder,
        Escaper $escaper,
        RatingFactory $ratingFactory,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
        $this->request = $request;
        $this->reviewRepository = $reviewRepository;
        $this->reviewFactory = $reviewFactory;
        $this->customerRepository = $customerRepository;
        $this->urlBuilder = $urlBuilder;
        $this->escaper = $escaper;
        $this->ratingFactory = $ratingFactory;
    }

    /**
     * Get Data
     *
     * @return array
     * @throws LocalizedException
     */
    public function getData(): array
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $review = $this->getCurrentReview();
        $this->loadedData[$review->getId()] = $review->getData();
        $this->loadedData[$review->getId()]['author'] = $this->getAuthorInfo($review);
        $this->loadedData[$review->getId()]['data']['rating_summary'] = $this->getRatingSummary($review->getId());

        return $this->loadedData;
    }

    /**
     * Get Current Review
     *
     * @return ReviewInterface
     */
    private function getCurrentReview(): ReviewInterface
    {
        if ($reviewId = $this->getReviewId()) {
            try {
                $review = $this->reviewRepository->getById($reviewId);
            } catch (\Exception $e) {
                $review = $this->reviewFactory->create();
            }
            return $review;
        }

        return $this->reviewFactory->create();
    }

    /**
     * Get Review ID
     *
     * @return int
     */
    private function getReviewId(): int
    {
        return (int) $this->request->getParam($this->getRequestFieldName());
    }

    /**
     * Get Author Info
     *
     * @param ReviewInterface $review
     * @return string
     * @throws LocalizedException
     */
    private function getAuthorInfo(ReviewInterface $review): string
    {
        try {
            $customer = $this->customerRepository->getById($review->getCustomerId());
            $customerText = __(
                '<a href="%1" onclick="this.target=\'blank\'">%2 %3</a> <a href="mailto:%4">(%4)</a>',
                $this->urlBuilder->getUrl(
                    'customer/index/edit',
                    ['id' => $customer->getId(), 'active_tab' => 'review']
                ),
                $this->escaper->escapeHtml($customer->getFirstname()),
                $this->escaper->escapeHtml($customer->getLastname()),
                $this->escaper->escapeHtml($customer->getEmail())
            );
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $customerText = ($review->getStoreId() == Store::DEFAULT_STORE_ID)
                ? __('Administrator') : __('Guest');
        }
        return $customerText;
    }

    /**
     * Get Rating Summary
     *
     * @param int $reviewId
     * @return array
     */
    private function getRatingSummary(int $reviewId): array
    {
        $reviewData = $this->ratingFactory->create()->getReviewSummary($reviewId);
        return [
            'sum'   => $reviewData->getSum(),
            'count' => $reviewData->getCount(),
        ];
    }
}
