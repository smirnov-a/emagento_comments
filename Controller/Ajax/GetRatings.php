<?php

namespace Local\Comments\Controller\Ajax;

use Local\Comments\Helper\Reviews;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
//use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Serialize\SerializerInterface;

class GetRatings extends \Magento\Framework\App\Action\Action implements /*HttpGetActionInterface*/ HttpPostActionInterface
{
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
            'options' => [],
        ];
        /* нужно вернуть json в таком виде
        {code: 1, label: 'очень плохо'},
        {code: 2, label: 'плохо'},
        {code: 3, label: 'средне'},
        {code: 4, label: 'хорошо'},
        {code: 5, label: 'отлично'}
        */
        $values = [
            1 => 'очень плохо',
            2 => 'плохо',
            3 => 'средне',
            4 => 'хорошо',
            5 => 'отлично',
        ];
        // взять код рейтинга для магазина из конфига
        $ratingId = $this->_scopeConfig->getValue('local_comments/settings/rating_id', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);   // 6
        $response['rating_id'] = $ratingId;
        // и опции для него из rating_options
        /** @var \Magento\Review\Model\ResourceModel\Rating\Option\Collection $collection */
        $collection = $this->_optionFactory->create();
        $collection
            ->addRatingFilter($ratingId)
            ->setPositionOrder()
            ->load();
        //echo $collection->getSelect(); exit;
        //$qq = $collection->getItemsByColumnValue('rating_id', $ratingId); //echo gettype($qq); var_dump($qq); exit;
        //$this->logger->info(serialize($qq));

        foreach ($collection as $option) {
            $response['options'][] = [
                'code' => $option->getId(),
                'label' => $values[$option->getValue()] ?? '__',
                'value' => $option->getValue(),
            ];
        }
        //var_dump($response); exit;
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData($response);
    }
}
