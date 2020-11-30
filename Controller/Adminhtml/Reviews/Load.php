<?php

namespace Emagento\Comments\Controller\Adminhtml\Reviews;

//use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;

class Load extends \Magento\Backend\App\Action //implements HttpPostActionInterface
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_objectManager = $objectmanager;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Json
     */
    public function execute()
    {
        $cnt = 0;
        foreach (['Flamp', 'Yandex'] as $remote) {
            $class = 'Emagento\Comments\Model\Remote\\' . $remote;
            $job = $this->_objectManager->create($class);

            $cnt += $job->getComments();
        }
        $resultJson = $this->resultJsonFactory->create();

        return $resultJson->setData(['success' => true, 'processed' => $cnt]);
    }
}