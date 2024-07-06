<?php

namespace Emagento\Comments\Api\Data\Review;

interface ReplyDataInterface
{
    public const REVIEW_ID   = 'review_id';
    public const CUSTOMER_ID = 'customer_id';
    public const DETAIL      = 'detail';
    public const DETAIL_ID   = 'detail_id';
    public const LEVEL       = 'level';
    public const NICKNAME    = 'nickname';
    public const TITLE       = 'title';
    public const CREATED_AT  = 'created_at';
    public const LOGO_IMAGE  = 'logo_image';

    /**
     * Get Review ID
     *
     * @return string|null
     */
    public function getReviewId(): ?string;

    /**
     * Set Review ID
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setReviewId(?string $value): ReplyDataInterface;

    /**
     * Get Customer ID
     *
     * @return string|null
     */
    public function getCustomerId(): ?string;

    /**
     * Set Customer ID
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setCustomerId(?string $value): ReplyDataInterface;

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
     *
     * @return ReplyDataInterface
     */
    public function setDetail(?string $value): ReplyDataInterface;

    /**
     * Get Detail ID
     *
     * @return string|null
     */
    public function getDetailId(): ?string;

    /**
     * Set Detail ID
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setDetailId(?string $value): ReplyDataInterface;

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
     * @return ReplyDataInterface
     */
    public function setLevel(?string $value): ReplyDataInterface;

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
     * @return ReplyDataInterface
     */
    public function setNickname(?string $value): ReplyDataInterface;

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
     * @return ReplyDataInterface
     */
    public function setTitle(?string $value): ReplyDataInterface;

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
     * @return ReplyDataInterface
     */
    public function setCreatedAt(?string $value): ReplyDataInterface;

    /**
     * Get Logo Image Path
     *
     * @return string|null
     */
    public function getLogoImage(): ?string;

    /**
     * Set Logo Image Path
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setLogoImage(?string $value): ReplyDataInterface;
}
