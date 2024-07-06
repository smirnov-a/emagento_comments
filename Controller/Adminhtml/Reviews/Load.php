<?php

namespace Emagento\Comments\Controller\Adminhtml\Reviews;

use Exception;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Emagento\Comments\Helper\Data as Helper;
use Emagento\Comments\Model\Review\Remote\Processor as RemoteProcessor;

class Load extends Action
{
    /** @var JsonFactory */
    private JsonFactory $resultJsonFactory;
    /** @var RemoteProcessor */
    private RemoteProcessor $processor;
    /** @var Helper */
    private Helper $helper;

    /**
     * @param Context $context
     * @param JsonFactory $resultJsonFactory
     * @param RemoteProcessor $processor
     * @param Helper $helper
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        RemoteProcessor $processor,
        Helper $helper
    ) {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->processor = $processor;
        $this->helper = $helper;
    }

    /**
     * Execute
     *
     * @throws Exception
     */
    public function execute(): Json
    {
        $cnt = 0;
        foreach ($this->helper->getRemoteTypes() as $type) {
            $cnt += $this->processor->processRemoteReviews($type);
        }

        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'success'   => true,
            'processed' => $cnt,
        ]);
    }
}
