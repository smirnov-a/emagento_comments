<?php

namespace Local\Comments\Controller\Ajax;

use Local\Comments\Helper\Reviews;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
//use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\SerializerInterface;
//use Magento\Framework\App\RequestInterface;

class Getlist extends \Magento\Framework\App\Action\Action implements /*HttpGetActionInterface*/ HttpPostActionInterface
{
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
    private $logger;
    //protected $request;

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
        // кол-во может прийти параметром (по умолчанию 5)
        //$post = $this->getRequest()->getPost(); $this->logger->info('post: '.serialize($post));
        //$post = $this->getRequest()->getParams();   //var_dump($post); exit;
        //$this->logger->info(__METHOD__.'; post: '.serialize($post));
        //$count = (int)$this->getRequest()->getParam('count', 5); //var_dump($count); exit;
        $count = $this->getRequest()->getParam('count', 5);
        $data = $this->reviewsHelper->getReviewList($count)->toArray();   //var_dump($data); exit;
        $response = $this->serializer->serialize($data);    //var_dump($response); exit;

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
