<?php

namespace Emagento\Comments\Cron;

use Emagento\Comments\Helper\Data as Helper;
use Emagento\Comments\Model\Review\Remote\Processor as RemoteProcessor;
use Psr\Log\LoggerInterface;

class GetRemoteComments
{
    /** @var Helper */
    private Helper $helper;
    /** @var RemoteProcessor */
    private RemoteProcessor $processor;
    /** @var LoggerInterface */
    private LoggerInterface $logger;

    /**
     * @param Helper $helper
     * @param RemoteProcessor $processor
     * @param LoggerInterface $logger
     */
    public function __construct(
        Helper $helper,
        RemoteProcessor $processor,
        LoggerInterface $logger
    ) {
        $this->helper = $helper;
        $this->processor = $processor;
        $this->logger = $logger;
    }

    /**
     * Execute
     *
     * @return void
     */
    public function execute(): void
    {
        if (!$this->helper->isCronEnabled()) {
            return;
        }

        foreach ($this->helper->getRemoteTypes() as $type) {
            try {
                $this->processor->processRemoteReviews($type);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
