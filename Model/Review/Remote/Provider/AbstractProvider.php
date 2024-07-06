<?php

namespace Emagento\Comments\Model\Review\Remote\Provider;

use Emagento\Comments\Api\ReviewRepositoryInterface;
use Emagento\Comments\Api\ReviewProviderInterface;
use Emagento\Comments\Api\ReviewInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\FilterManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory as OptionCollection;
use Psr\Log\LoggerInterface;
use Emagento\Comments\Helper\Data as EmagentoHelper;
use Magento\Framework\HTTP\ClientInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Emagento\Comments\Model\Service\EmailSender;
use Emagento\Comments\Model\Review;
use Magento\Review\Model\RatingFactory;

abstract class AbstractProvider implements ReviewProviderInterface
{
    protected const ROBOT_TITLE = '_robot_';
    protected const ANONYMOUS = 'Guest';

    /** @var StoreManagerInterface */
    private StoreManagerInterface $storeManager;
    /** @var EmailSender */
    private EmailSender $emailSender;
    /** @var OptionCollection */
    private OptionCollection $optionCollection;
    /** @var int|null */
    private ?int $storeReviewEntity = null;
    /** @var int|null */
    private ?int $ratingId = null;
    /** @var LoggerInterface */
    private LoggerInterface $logger;
    /** @var ClientInterface */
    private ClientInterface $httpClient;
    /** @var Json */
    private Json $jsonSerializer;
    /** @var int */
    private int $processedItems = 0;
    /** @var array */
    private array $newItems = [];
    /** @var array|null */
    private ?array $ratingOptions = null;
    /** @var EmagentoHelper  */
    protected EmagentoHelper $helper;
    /** @var Escaper */
    protected Escaper $escaper;
    /** @var FilterManager */
    protected FilterManager $filterManager;
    /** @var ReviewRepositoryInterface */
    protected ReviewRepositoryInterface $reviewRepository;
    /** @var DateTime */
    protected DateTime $dateTime;
    /** @var int */
    protected int $storeId;
    /** @var array */
    protected array $stores;
    /** @var RatingFactory */
    protected RatingFactory $ratingFactory;

    /**
     * @param ReviewRepositoryInterface $reviewRepository
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param ClientInterface $httpClient
     * @param EmagentoHelper $helper
     * @param EmailSender $emailSender
     * @param Json $jsonSerializer
     * @param Escaper $escaper
     * @param FilterManager $filterManager
     * @param DateTime $dateTime
     * @param OptionCollection $optionCollection
     * @param RatingFactory $ratingFactory
     * @param int|null $storeId
     * @param array|null $stores
     * @throws NoSuchEntityException
     */
    public function __construct(
        ReviewRepositoryInterface $reviewRepository,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        ClientInterface $httpClient,
        EmagentoHelper $helper,
        EmailSender $emailSender,
        Json $jsonSerializer,
        Escaper $escaper,
        FilterManager $filterManager,
        DateTime $dateTime,
        OptionCollection $optionCollection,
        RatingFactory $ratingFactory,
        ?int $storeId = null,
        ?array $stores = null
    ) {
        $this->reviewRepository = $reviewRepository;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->httpClient = $httpClient;
        $this->helper = $helper;
        $this->emailSender = $emailSender;
        $this->jsonSerializer = $jsonSerializer;
        $this->escaper = $escaper;
        $this->filterManager = $filterManager;
        $this->dateTime = $dateTime;
        $this->optionCollection = $optionCollection;
        $this->ratingFactory = $ratingFactory;
        $this->storeId = $storeId !== null ? $storeId : $this->getStoreId();
        $this->stores = $stores !== null ? $stores : $this->getStores();
    }

    /**
     * Get Service Url
     *
     * @return string
     */
    abstract protected function getUrl(): string;

    /**
     * Check Errors
     *
     * @param mixed $response
     * @return bool
     */
    abstract protected function checkErrors($response): bool;

    /**
     * Process Comments
     *
     * @param array $comments
     * @return void
     */
    abstract protected function processComments(array $comments): void;

    /**
     * Do the Request
     *
     * @return array|bool|float|int|string|null
     */
    protected function doRequest(): mixed
    {
        $this->httpClient->get($this->getUrl());
        $response = $this->httpClient->getBody();

        return $this->jsonSerializer->unserialize($response);
    }

    /**
     * Get Parameters for Request
     *
     * @return array
     */
    protected function getParams(): array
    {
        return [];
    }

    /**
     * Load And Process Reviews
     *
     * @return int
     */
    public function loadAndProcessComments(): int
    {
        if (!$this->helper->isEnabled(static::REMOTE_TYPE)) {
            return 0;
        }

        $response = $this->doRequest();
        if (!$this->checkErrors($response)) {
            $msgError = 'Check error. response: ' . is_array($response) ? json_encode($response) : $response;
            $this->logger->error($msgError);
            return 0;
        }

        $this->processComments($response);
        $this->sendEmailNotification();

        return $this->processedItems;
    }

    /**
     * Save the Review
     *
     * @param Review $review
     * @param array $data
     * @param bool $isNew
     * @param bool $isReply
     * @param int|null $parentId
     * @return void
     */
    protected function saveReview(
        ReviewInterface|Review $review,
        array $data,
        bool $isNew,
        bool $isReply = false,
        ?int $parentId = null
    ): void {
        if (empty($data)) {
            return;
        }

        $review->addData($data);
        $review = $this->reviewRepository->save($review);
        $this->processedItems++;
        $msgLog = ucfirst(static::REMOTE_TYPE) . ': ';
        if ($isNew) {
            $review->aggregate();
            $this->newItems[] = $review->getId();
            $msgLog .= $isReply
                ? 'save reply id: ' . $data['source_id']  . ' on parent comment id: ' . $parentId
                : 'save new comment id: ' . $review->getId();
        } else {
            $msgLog .= 'update comment id: ' . $review->getId();
        }
        $this->logger->info($msgLog);
    }

    /**
     * Get Store Review Entity ID
     *
     * @return int
     */
    protected function getStoreReviewEntityId(): int
    {
        if ($this->storeReviewEntity === null) {
            $this->storeReviewEntity = $this->helper->getStoreReviewEntityId();
        }
        return $this->storeReviewEntity;
    }

    /**
     * Get Store ID
     *
     * @return int
     * @throws NoSuchEntityException
     */
    private function getStoreId(): int
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Get Store Array
     *
     * @return array
     */
    private function getStores(): array
    {
        $stores = [];
        foreach ($this->storeManager->getStores() as $store) {
            $stores[] = $store->getId();
        }

        return $stores;
    }

    /**
     * Send Email Notification
     *
     * @return void
     */
    public function sendEmailNotification(): void
    {
        if (empty($this->newItems)
            || !$this->helper->isNotificationEnabled()
        ) {
            return;
        }

        $emailData = [
            'counter'     => count($this->newItems),
            'new_reviews' => join(',', $this->newItems),
        ];
        $this->emailSender->sendEmail($emailData);
    }

    /**
     * Get Rating ID
     *
     * @return int
     */
    protected function getRatingId(): int
    {
        if ($this->ratingId === null) {
            $this->ratingId = $this->helper->getStoreRatingId();
        }

        return $this->ratingId;
    }

    /**
     * Get Rating Options
     *
     * @return array
     */
    protected function getRatingOptions(): array
    {
        if ($this->ratingOptions !== null) {
            return $this->ratingOptions;
        }
        $ratingId = $this->getRatingId();
        $collection = $this->optionCollection->create();
        $collection->addRatingFilter($ratingId)
            ->setPositionOrder();
        foreach ($collection as $option) {
            $this->ratingOptions[$ratingId][$option->getValue()] = $option->getOptionId();
        }

        return $this->ratingOptions;
    }
}
