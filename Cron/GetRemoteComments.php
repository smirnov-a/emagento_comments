<?php

namespace Emagento\Comments\Cron;

/**
 * Retrieve comments from Yandex/Flamp/etc
 */
class GetRemoteComments
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    private $_objectManager;

    /**
     * GetRemoteComments constructor.
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectmanager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager
    ) {
        $this->_objectManager = $objectmanager;
    }

    /**
     * Cron job method to clean old cache resources
     *
     * @return void
     */
    public function execute()
    {
        $cnt = 0;
        foreach (['Flamp', 'Yandex'] as $remote) {
            $class = 'Emagento\Comments\Model\Remote\\' . $remote;
            $job = $this->_objectManager->create($class);

            $cnt += $job->getComments();
        }
    }
}
