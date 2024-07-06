<?php

namespace Emagento\Comments\Model\Review\Remote;

use Emagento\Comments\Exception\UnknownSourceException;
use Emagento\Comments\Model\Review\Remote\Provider\AbstractProvider;
use Emagento\Comments\Model\Review\Remote\Provider\FlampProviderFactory;
use Emagento\Comments\Model\Review\Remote\Provider\YandexProviderFactory;
use Emagento\Comments\Helper\Constants;

class ProviderFactory
{
    /** @var YandexProviderFactory */
    private YandexProviderFactory $yandexProviderFactory;
    /** @var FlampProviderFactory */
    private FlampProviderFactory $flampProviderFactory;

    /**
     * @param YandexProviderFactory $yandexProviderFactory
     * @param FlampProviderFactory $flampProviderFactory
     */
    public function __construct(
        YandexProviderFactory $yandexProviderFactory,
        FlampProviderFactory $flampProviderFactory
    ) {
        $this->yandexProviderFactory = $yandexProviderFactory;
        $this->flampProviderFactory = $flampProviderFactory;
    }

    /**
     * Get Provider
     *
     * @param string $sourceId
     * @return AbstractProvider
     * @throws UnknownSourceException
     */
    public function getProvider(string $sourceId): AbstractProvider
    {
        switch ($sourceId) {
            case Constants::TYPE_YANDEX:
                return $this->yandexProviderFactory->create();
            case Constants::TYPE_FLAMP:
                return $this->flampProviderFactory->create();
            default:
                throw new UnknownSourceException("Unknown source: {$sourceId}");
        }
    }
}
