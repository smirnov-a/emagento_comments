<?php

namespace Emagento\Comments\Model\Data\Review;

use Magento\Framework\Api\AbstractSimpleObject;
use Emagento\Comments\Api\Data\Review\ReplyDataInterface;

class ReplyData extends AbstractSimpleObject implements ReplyDataInterface
{
    /**
     * Get Review ID
     *
     * @return string|null
     */
    public function getReviewId(): ?string
    {
        return $this->_get(self::REVIEW_ID);
    }

    /**
     * Set Review ID
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setReviewId(?string $value): ReplyDataInterface
    {
        return $this->setData(self::REVIEW_ID, $value);
    }

    /**
     * Get Customer ID
     *
     * @return string|null
     */
    public function getCustomerId(): ?string
    {
        return $this->_get(self::CUSTOMER_ID);
    }

    /**
     * Set Customer ID
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setCustomerId(?string $value): ReplyDataInterface
    {
        return $this->setData(self::CUSTOMER_ID, $value);
    }

    /**
     * Get Review Detail
     *
     * @return string|null
     */
    public function getDetail(): ?string
    {
        return $this->_get(self::DETAIL);
    }

    /**
     * Set Review Detail
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setDetail(?string $value): ReplyDataInterface
    {
        return $this->setData(self::DETAIL, $value);
    }

    /**
     * Get Detail ID
     *
     * @return string|null
     */
    public function getDetailId(): ?string
    {
        return $this->_get(self::DETAIL_ID);
    }

    /**
     * Set Detail ID
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setDetailId(?string $value): ReplyDataInterface
    {
        return $this->setData(self::DETAIL_ID, $value);
    }

    /**
     * Get Level
     *
     * @return string|null
     */
    public function getLevel(): ?string
    {
        return $this->_get(self::LEVEL);
    }

    /**
     * Set Level
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setLevel(?string $value): ReplyDataInterface
    {
        return $this->setData(self::LEVEL, $value);
    }

    /**
     * Get Nickname
     *
     * @return string|null
     */
    public function getNickname(): ?string
    {
        return $this->_get(self::NICKNAME);
    }

    /**
     * Set Nickname
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setNickname(?string $value): ReplyDataInterface
    {
        return $this->setData(self::NICKNAME, $value);
    }

    /**
     * Get Title
     *
     * @return string|null
     */
    public function getTitle(): ?string
    {
        return $this->_get(self::TITLE);
    }

    /**
     * Set Title
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setTitle(?string $value): ReplyDataInterface
    {
        return $this->setData(self::TITLE, $value);
    }

    /**
     * Get Created At
     *
     * @return string|null
     */
    public function getCreatedAt(): ?string
    {
        return $this->_get(self::CREATED_AT);
    }

    /**
     * Set Created At
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setCreatedAt(?string $value): ReplyDataInterface
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * Get Logo Image Path
     *
     * @return string|null
     */
    public function getLogoImage(): ?string
    {
        return $this->_get(self::LOGO_IMAGE);
    }

    /**
     * Set Logo Image Path
     *
     * @param string|null $value
     * @return ReplyDataInterface
     */
    public function setLogoImage(?string $value): ReplyDataInterface
    {
        return $this->setData(self::LOGO_IMAGE, $value);
    }
}
