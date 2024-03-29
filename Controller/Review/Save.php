<?php

namespace Emagento\Comments\Controller\Review;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
//use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Data\Form\FormKey\Validator;

class Save extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface /*HttpGetActionInterface*/
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $formKeyValidator;
    /**
     * Review model
     *
     * @var \Emagento\Comments\Model\ReviewFactory
     */
    protected $reviewFactory;
    /**
     * Rating model
     *
     * @var \Magento\Review\Model\RatingFactory
     */
    protected $ratingFactory;
    /**
     * Core model store manager interface
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;
    /**
     * @var \Magento\Framework\Session\SessionManagerInterface
     */
    protected $_coreSession;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
    /**
     * @var \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory
     */
    protected $_optionFactory;

    /**
     * Init controller
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param SerializerInterface $serializer
     * @param Validator $formKeyValidator
     * @param \Emagento\Comments\Model\ReviewFactory $reviewFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Customer\Model\Session $session
     * @param \Magento\Framework\Session\SessionManagerInterface $coreSession
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $optionFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SerializerInterface $serializer,
        Validator $formKeyValidator,
        \Emagento\Comments\Model\ReviewFactory $reviewFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $optionFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializer = $serializer;
        $this->formKeyValidator = $formKeyValidator;
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->_customerSession = $session;
        $this->_coreSession = $coreSession;
        $this->_scopeConfig = $scopeConfig;
        $this->_optionFactory = $optionFactory;
    }

    /**
     * Execute
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $result = [
            'error' => __('Please try again later'),
        ];
        if ($this->formKeyValidator->validate($this->getRequest())) {
            $data = $this->getRequest()->getPostValue();
            $rating = $this->getRequest()->getParam('ratings', []);

            $review = $this->reviewFactory->create()->setData($data);
            $review->unsetData('review_id')
                ->setTitle('_auto_');
            $validate = $review->validate();
            if ($validate === true) {
                $nickname = trim($data['nickname']);
                $productId = 0;
                $customerId = null;
                try {
                    $review->setEntityPkValue($productId)
                        ->setEntityId(\Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)
                        ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)
                        ->setCustomerId($customerId)
                        ->setNickname($nickname)
                        ->setStoreId($this->storeManager->getStore()->getId())
                        ->setStores([$this->storeManager->getStore()->getId()])
                        ->save();

                    $ratingOptions = [];
                    $ratingId = $this->_scopeConfig->getValue(
                        'local_comments/settings/rating_id',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );

                    /** @var \Magento\Review\Model\ResourceModel\Rating\Option\Collection $collection */
                    $collection = $this->_optionFactory->create();
                    $collection
                        ->addRatingFilter($ratingId)
                        ->setPositionOrder();
                    foreach ($collection as $option) {
                        $ratingOptions[$ratingId][] = $option->getId();
                    }
                    if (isset($rating[$ratingId]) && in_array($rating[$ratingId], $ratingOptions[$ratingId])) {
                        $_vote = $rating[$ratingId];
                        try {
                            $this->ratingFactory->create()
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->setCustomerId($customerId)
                                ->addOptionVote($_vote, $productId);
                        } catch (\Exception $e) {
                            $this->logger->error('Error append rating: '.$e->getMessage());
                        }
                    }

                    $review->aggregate();
                    $result['message'] = __('Thank you. Your review is awaiting moderator review');
                    unset($result['error']);
                } catch (\Exception $e) {
                    $result['error'] = $e->getMessage();
                    $this->logger->error('Error append review: ' . $e->getMessage());
                }
            } else {
                $msg = '';
                if (is_array($validate)) {
                    foreach ($validate as $errorMsg) {
                        $msg = $errorMsg;
                        break;
                    }
                } else {
                    $msg = __('Please try again later');
                }
                $result['error'] = $msg;
                $this->logger->error($msg);
            }
        } else {
            $result['error'] = __('Form error. Please try again later');
            $this->logger->error('formKeyValidator error');
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
