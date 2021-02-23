<?php

declare(strict_types=1);

//use Magento\Framework\Api\DataObjectHelper;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Review\Model\ResourceModel\Review;
use Magento\Review\Model\ResourceModel\Rating;

$objectManager = Bootstrap::getObjectManager();
/** @var Review $reviewResource */
$reviewResource = $objectManager->create(Review::class);
$reviewEntityId = $reviewResource->getEntityIdByCode('store');
if (!$reviewEntityId) {
    $reviewResource->getConnection()->insertForce(
        $reviewResource->getTable('review_entity'),
        [
            'entity_id' => \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE,
            'entity_code' => 'store',
        ]
    );
    $reviewEntityId = \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE;
}

/** @var Rating $rating */
$ratingResource = $objectManager->create(Rating::class);
$ratingEntityId = $ratingResource->getEntityIdByCode('store');
if (!$ratingEntityId) {
    $ratingResource->getConnection()->insert(
        $ratingResource->getTable('rating_entity'),
        [
            'entity_id' => \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE,
            'entity_code' => 'store'
        ]
    );
    $ratingEntityId = \Emagento\Comments\Helper\Data::REVIEW_ENTITY_TYPE_STORE;
}
