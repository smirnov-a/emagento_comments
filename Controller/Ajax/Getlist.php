<?php

namespace Emagento\Comments\Controller\Ajax;

use Emagento\Comments\Helper\Reviews;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
//use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\SerializerInterface;

class Getlist extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /* implements HttpGetActionInterface*/
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var Reviews
     */
    protected $reviewsHelper;
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    //protected $request;
    /**
     * @var string
     */
    protected $_pageVarName = 'p';

    /**
     * @var string
     */
    protected $_limitVarName = 'limit';
    /**
     * @var int
     */
    protected $_limit;
    /**
     * The list of available pager limits
     *
     * @var array
     */
    protected $_availableLimit = [2 => 2, 5 => 5, 15 => 15];

    /**
     * Init controller
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param SerializerInterface $serializer
     * @param Reviews $reviewsHelper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        SerializerInterface $serializer,
        Reviews $reviewsHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->serializer = $serializer;
        $this->reviewsHelper = $reviewsHelper;
        $this->logger = $logger;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $limit = $this->getLimit();
        $page = $this->getCurrentPage();
        $collection = $this->reviewsHelper->getReviewList($page, $limit);
        //$this->logger->info('size: '.$size);
        $response = $this->serializer->serialize($collection->toArray());

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData($response);
    }

    public function getLimit()
    {
        if ($this->_limit !== null) {
            return $this->_limit;
        }

        $limits = $this->getAvailableLimit();
        if ($limit = $this->getRequest()->getParam($this->getLimitVarName())) {
            if (isset($limits[$limit])) {
                return $limit;
            }
        }

        $limits = array_keys($limits);
        return $limits[0];
    }

    /**
     * Retrieve pager limit
     *
     * @return array
     */
    public function getAvailableLimit()
    {
        return $this->_availableLimit;
    }

    /**
     * Retrieve name for pager limit data
     *
     * @return string
     */
    public function getLimitVarName()
    {
        return $this->_limitVarName;
    }

    /**
     * Return current page
     *
     * @return int
     */
    public function getCurrentPage()
    {
        return (int)$this->getRequest()->getParam($this->getPageVarName(), 1);
    }

    /**
     * Get page variable name
     *
     * @return string
     */
    public function getPageVarName()
    {
        return $this->_pageVarName;
    }
}
