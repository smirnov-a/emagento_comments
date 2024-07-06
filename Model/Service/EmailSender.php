<?php

namespace Emagento\Comments\Model\Service;

use Emagento\Comments\Helper\Data as EmagentoHelper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class EmailSender
{
    private const TEMPLATE_ID = 'local_comments_notify_template';

    /** @var EmagentoHelper */
    private EmagentoHelper $helper;
    /** @var LoggerInterface */
    private LoggerInterface $logger;
    /** @var StoreManagerInterface */
    private StoreManagerInterface $storeManager;
    /** @var TransportBuilder */
    private TransportBuilder $transportBuilder;

    /**
     * @param LoggerInterface $logger
     * @param EmagentoHelper $helper
     * @param StoreManagerInterface $storeManager
     * @param TransportBuilder $transportBuilder
     */
    public function __construct(
        LoggerInterface $logger,
        EmagentoHelper $helper,
        StoreManagerInterface $storeManager,
        TransportBuilder $transportBuilder,
    ) {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->transportBuilder = $transportBuilder;
    }

    /**
     * Send Email
     *
     * @param array $emailData
     * @return void
     */
    public function sendEmail(array $emailData): void
    {
        try {
            $recipients = $this->helper->getNotifyAddresses();
            $this->transportBuilder
                ->setTemplateIdentifier(self::TEMPLATE_ID)
                ->setTemplateOptions(['area' => 'frontend', 'store' => $this->storeManager->getStore()->getId()])
                ->setTemplateVars($emailData)
                ->addTo($recipients)
                ->setFromByScope('general')
                ->getTransport()
                ->sendMessage();

            $this->logger->info('Sent email notification to: ' . join(',', $recipients));

        } catch (\Exception $e) {
            $this->logger->error(
                'Failed to send the notification email'
                . PHP_EOL
                . $e->getMessage()
                . PHP_EOL
                . $e->getTraceAsString()
                . PHP_EOL
            );
        }
    }
}
