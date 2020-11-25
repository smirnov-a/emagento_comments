<?php

namespace Local\Comments\Controller\Review;

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
     * @var \Local\Comments\Model\ReviewFactory
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
     * @param \Local\Comments\Model\ReviewFactory $reviewFactory
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
        \Local\Comments\Model\ReviewFactory $reviewFactory,
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
            'error' => 'Произошла ошибка. Попробуйте позже',
        ];
        if ($this->formKeyValidator->validate($this->getRequest())) {
            $data = $this->getRequest()->getPostValue();
            $rating = $this->getRequest()->getParam('ratings', []);     // ratings[6]=23

            $review = $this->reviewFactory->create()->setData($data);
            $review->unsetData('review_id')
                ->setTitle('_auto_');
            $validate = $review->validate();
            if ($validate === true) {
                $nickname = trim($data['nickname']);
                // записать его в сессию (не надо это в storage на стороне клиента)
                //$this->_coreSession->setReviewUserName($nickname);
                $productId = 0;
                $customerId = null;
                try {
                    $review->setEntityPkValue($productId)    // код товара в данном случае 0
                        ->setEntityId(\Local\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE)
                        ->setStatusId(\Magento\Review\Model\Review::STATUS_PENDING)
                        ->setCustomerId($customerId)
                        ->setNickname($nickname)
                        ->setStoreId($this->storeManager->getStore()->getId())
                        ->setStores([$this->storeManager->getStore()->getId()])
                        ->save();
                    // дальше рейтинг
                    $ratingOptions = [];
                    // код рейтинга из конфига (6)
                    $ratingId = $this->_scopeConfig->getValue(
                        'local_comments/settings/rating_id',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                    // получить его опции
                    /** @var \Magento\Review\Model\ResourceModel\Rating\Option\Collection $collection */
                    $collection = $this->_optionFactory->create();
                    $collection
                        ->addRatingFilter($ratingId)
                        ->setPositionOrder();
                    foreach ($collection as $option) {
                        $ratingOptions[$ratingId][] = $option->getId();     // [6 => [21, 22, 23, 24, 25]]
                    }
                    //$ratingOptions = [
                    //    1 => [1, 2, 3, 4, 5], // <== Look at your database table `rating_option` for these vals
                    //    //2 => [6, 7, 8, 9, 10],
                    //    //3 => [11, 12, 13, 14, 15]
                    //];
                    //foreach ($ratingOptions as $ratingId => $optionIds) {
                    if (isset($rating[$ratingId]) && in_array($rating[$ratingId], $ratingOptions[$ratingId])) {
                        $_vote = $rating[$ratingId];
                        try {
                            $this->ratingFactory->create()
                                ->setRatingId($ratingId)
                                ->setReviewId($review->getId())
                                ->setCustomerId($customerId)
                                ->addOptionVote($_vote, $productId);
                        } catch (Exception $e) {
                            $this->logger->error('Error append rating: '.$e->getMessage());
                        }
                    }
                    //}
                    $review->aggregate();
                    $result['message'] = 'Спасибо! Ваш отзыв ожидает проверки модератором';
                    unset($result['error']);
                } catch (\Exception $e) {
                    $result['error'] = $e->getMessage();    //'Ошибка данных. Попробуйте позже';
                    $this->logger->error('Error append review: '.$e->getMessage());
                }
            } else {
                $msg = '';
                if (is_array($validate)) {
                    foreach ($validate as $errorMsg) {
                        $msg = $errorMsg;
                        break;
                    }
                } else {
                    $msg = 'Ошибка данных. Попробуйте позже';
                }
                $result['error'] = $msg;
                $this->logger->error($msg);
            }
        } else {
            $result['error'] = 'Ошибка формы. Попробуйте позже';
            $this->logger->error('formKeyValidator error');
        }
        //$response = $this->serializer->serialize($result);

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($result);
    }
}
