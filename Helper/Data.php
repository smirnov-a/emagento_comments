<?php

namespace Emagento\Comments\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\UrlInterface;
use Emagento\Comments\Api\ReviewEntityRepositoryInterface;
use Emagento\Comments\Api\RatingRepositoryInterface;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    /** @var UrlInterface */
    protected UrlInterface $baseUrl;
    /** @var ReviewEntityRepositoryInterface */
    protected ReviewEntityRepositoryInterface $reviewEntityRepository;
    /** @var RatingRepositoryInterface */
    protected RatingRepositoryInterface $ratingRepository;

    /**
     * @param Context $context
     * @param UrlInterface $baseUrl
     * @param ReviewEntityRepositoryInterface $reviewEntityRepository
     * @param RatingRepositoryInterface $ratingRepository
     */
    public function __construct(
        Context $context,
        UrlInterface $baseUrl,
        ReviewEntityRepositoryInterface $reviewEntityRepository,
        RatingRepositoryInterface $ratingRepository
    ) {
        parent::__construct($context);
        $this->baseUrl = $baseUrl;
        $this->reviewEntityRepository = $reviewEntityRepository;
        $this->ratingRepository = $ratingRepository;
    }

    /**
     * Get Source Option Array
     *
     * @return array[]
     */
    public function getSourceOptionArray(): array
    {
        $local = Constants::TYPE_LOCAL;
        $flamp = Constants::TYPE_FLAMP;
        $yandex = Constants::TYPE_YANDEX;
        return [
            ['value' => Constants::TYPE_LOCAL,  'label' => __(ucfirst($local))],
            ['value' => Constants::TYPE_FLAMP,  'label' => __(ucfirst($flamp))],
            ['value' => Constants::TYPE_YANDEX, 'label' => __(ucfirst($yandex))],
        ];
    }

    /**
     * Get Store Entity ID
     *
     * @return int
     */
    public function getStoreReviewEntityId(): int
    {
        try {
            return (int)$this->reviewEntityRepository
                ->getEntityIdByCode(Constants::REVIEW_ENTITY_TYPE_BY_STORE);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Get Store Rating ID
     *
     * @return int
     */
    public function getStoreRatingId(): int
    {
        if ($ratingId = (int) $this->getConfigValue('rating_id', 'general')) {
            return $ratingId;
        }

        try {
            return (int)$this->ratingRepository
                ->getRatingIdByCode(Constants::REVIEW_ENTITY_TYPE_BY_STORE);
        } catch (\Throwable $e) {
            return 0;
        }
    }

    /**
     * Get Logo Image Array
     *
     * @return string[]
     */
    public function getLogoImagesArray(): array
    {
        $baseUrl = $this->baseUrl->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]) . Constants::PATH_COMMENTS;

        return [
            Constants::TYPE_LOCAL  => $baseUrl . $this->getConfigValueLogoPath(Constants::TYPE_LOCAL),
            Constants::TYPE_FLAMP  => $baseUrl . $this->getConfigValueLogoPath(Constants::TYPE_FLAMP),
            Constants::TYPE_YANDEX => $baseUrl . $this->getConfigValueLogoPath(Constants::TYPE_YANDEX),
        ];
    }

    /**
     * Get Config Logo Path
     *
     * @param string $type
     * @return string
     */
    private function getConfigValueLogoPath(string $type): string
    {
        return (string) $this->getConfigValue('image_logo', $type);
    }

    /**
     * Get Config Value
     *
     * @param string $item
     * @param string $section
     * @return string|null
     */
    public function getConfigValue(string $item, string $section = Constants::TYPE_LOCAL)
    {
        $path = sprintf('%s/%s/%s', Constants::XML_CONFIG_PREFIX_PATH, $section, $item);
        return $this->scopeConfig->getValue($path, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get Is Enabled
     *
     * @param string $type
     * @return bool
     */
    public function isEnabled(string $type): bool
    {
        return (bool) $this->getConfigValue('is_enabled', $type);
    }

    /**
     * Get Is Guest Allowed
     *
     * @return bool
     */
    public function isGuestAllowReviews(): bool
    {
        return (bool) $this->getConfigValue('allow_guest');
    }

    /**
     * Get Notify Address Array
     *
     * @return array
     */
    public function getNotifyAddresses(): array
    {
        $emails = $this->getConfigValue('notification_email', 'general');
        if (!$emails) {
            return [];
        }

        return array_map('trim', explode(',', $emails));
    }

    /**
     * Get Is Notification Enabled
     *
     * @return bool
     */
    public function isNotificationEnabled(): bool
    {
        return (bool) $this->getConfigValue('notification_enabled', 'general');
    }

    /**
     * Get Is Cron Enabled
     *
     * @return bool
     */
    public function isCronEnabled(): bool
    {
        return (bool) $this->getConfigValue('cron_enabled', 'general');
    }

    /**
     * Get Remote Types
     *
     * @param bool $withAll
     * @return string[]
     */
    public function getRemoteTypes(bool $withAll = false): array
    {
        $types = [
            Constants::TYPE_YANDEX,
            Constants::TYPE_FLAMP,
        ];
        if ($withAll) {
            $types[] = Constants::TYPE_ALL;
        }

        return $types;
    }

    /**
     * Get Default Review Status Id
     *
     * @return int
     */
    public function getDefaultReviewStatusId(): int
    {
        return (int) $this->getConfigValue('default_status', 'general');
    }
}
