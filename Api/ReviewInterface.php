<?php

namespace Emagento\Comments\Api;

interface ReviewInterface
{
    public const REVIEW_ID = 'review_id';
    public const RATING_VOTES = 'rating_votes';
    public const PATH = 'path';
    public const SOURCE = 'source';
    public const SOURCE_ID = 'source_id';
    public const CREATED_AT = 'created_at';
    public const UPDATED_AT = 'updated_at';
    public const LEVEL = 'level';
    public const TITLE = 'title';
    public const DETAIL = 'detail';
    public const NICKNAME = 'nickname';
    public const PARENT_ID = 'parent_id';
    public const RATING = 'rating';
    public const STATUS_ID = 'status_id';
    public const ENTITY_ID = 'entity_id';

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getReviewId(): ?int;

    /**
     * Get Path of Review
     *
     * @return string|null
     */
    public function getPath(): ?string;

    /**
     * Set Path of Review
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setPath(?string $value): ReviewInterface;

    /**
     * Get Is Approved
     *
     * @return bool|null
     */
    public function isApproved();

    /**
     * Get Ratings Data
     *
     * @return array|null
     */
    public function getRatingsData(): ?array;

    /**
     * Get Author Info
     *
     * @return string|null
     */
    public function getAuthorInfo(): ?string;

    /**
     * Set Review Id
     *
     * @param int|null $value
     * @return ReviewInterface
     */
    public function setReviewId(?int $value): ReviewInterface;

    /**
     * Get Source
     *
     * @return string|null
     */
    public function getSource(): ?string;

    /**
     * Set Source
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setSource(?string $value): ReviewInterface;

    /**
     * Get Source ID
     *
     * @return string|null
     */
    public function getSourceId(): ?string;

    /**
     * Set Source ID
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setSourceId(?string $value): ReviewInterface;

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string;

    /**
     * Set Created At
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setCreatedAt(?string $value): ReviewInterface;

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string;

    /**
     * Set Updated At
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setUpdatedAt(?string $value): ReviewInterface;

    /**
     * Set Entity Primary Key Value
     *
     * @param int|null $value
     * @return ReviewInterface
     */
    public function setEntityPkValue(?int $value): ReviewInterface;

    /**
     * Set Customer ID
     *
     * @param int|null $value
     * @return ReviewInterface
     */
    public function setCustomerId(?int $value): ReviewInterface;

    /**
     * Set Status ID
     *
     * @param int $value
     * @return ReviewInterface
     */
    public function setStatusId(int $value): ReviewInterface;

    /**
     * Get Level
     *
     * @return string|null
     */
    public function getLevel(): ?string;

    /**
     * Set Level
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setLevel(?string $value): ReviewInterface;

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle(): ?string;

    /**
     * Set Title
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setTitle(?string $value): ReviewInterface;

    /**
     * Get Review Detail
     *
     * @return string|null
     */
    public function getDetail(): ?string;

    /**
     * Set Review Detail
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setDetail(?string $value): ReviewInterface;

    /**
     * Get Nickname
     *
     * @return string|null
     */
    public function getNickname(): ?string;

    /**
     * Set Nickname
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setNickname(?string $value): ReviewInterface;

    /**
     * Set Store ID
     *
     * @param int $value
     * @return ReviewInterface
     */
    public function setStoreId(int $value): ReviewInterface;

    /**
     * Set Stores
     *
     * @param array|null $value
     * @return ReviewInterface
     */
    public function setStores(?array $value): ReviewInterface;

    /**
     * Get Parent Review ID
     *
     * @return string|null
     */
    public function getParentId(): ?string;

    /**
     * Set Parent Review ID
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setParentId(?string $value): ReviewInterface;

    /**
     * Get Rating
     *
     * @return string|null
     */
    public function getRating(): ?string;

    /**
     * Set Rating
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setRating(?string $value): ReviewInterface;
}
