<?php

namespace Emagento\Comments\Model\Review\Remote;

class Processor
{
    /** @var ProviderFactory */
    private ProviderFactory $providerFactory;

    /**
     * @param ProviderFactory $providerFactory
     */
    public function __construct(
        ProviderFactory $providerFactory
    ) {
        $this->providerFactory = $providerFactory;
    }

    /**
     * Process Reviews
     *
     * @param string $type
     * @return int
     * @throws \Emagento\Comments\Exception\UnknownSourceException
     */
    public function processRemoteReviews(string $type): int
    {
        $provider = $this->providerFactory->getProvider($type);
        return $provider->loadAndProcessComments();
    }
}
