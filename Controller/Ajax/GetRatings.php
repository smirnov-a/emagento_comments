<?php

namespace Emagento\Comments\Controller\Ajax;

use Emagento\Comments\Helper\Reviews;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
//use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;

class GetRatings extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /* implements HttpGetActionInterface */
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
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
     *
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $optionFactory
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Review\Model\ResourceModel\Rating\Option\CollectionFactory $optionFactory
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_optionFactory = $optionFactory;
    }

    /**
     * Execute (local_reviews/ajax/getratings)
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $response = [
            'rating_id' => null,
            'options'   => [],
        ];

        $values = [
            1 => __('very bad'),
            2 => __('bad'),
            3 => __('medium'),
            4 => __('good'),
            5 => __('very good'),
        ];

        $ratingId = $this->_scopeConfig->getValue(
            'local_comments/settings/rating_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $response['rating_id'] = $ratingId;

        /** @var \Magento\Review\Model\ResourceModel\Rating\Option\Collection $collection */
        $collection = $this->_optionFactory->create();
        $collection
            ->addRatingFilter($ratingId)
            ->setPositionOrder()
            ->load();

        foreach ($collection as $option) {
            $response['options'][] = [
                'code'  => $option->getId(),
                'label' => $values[$option->getValue()] ?? '__',
                'value' => $option->getValue(),
            ];
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
