<?php

namespace Emagento\Comments\Model\Data\Review;

use Magento\Framework\Api\AbstractSimpleObject;
use Emagento\Comments\Api\Data\Review\ReviewInterface;

class Review extends AbstractSimpleObject implements ReviewInterface
{
    /**
     * Get Review ID
     *
     * @return int|null
     */
    public function getReviewId(): ?int
    {
        return $this->_get(self::REVIEW_ID);
    }

    /**
     * Set Review ID
     *
     * @param int|null $value
     * @return ReviewInterface
     */
    public function setReviewId(?int $value): ReviewInterface
    {
        return $this->setData(self::REVIEW_ID, $value);
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
     * @return ReviewInterface
     */
    public function setCreatedAt(?string $value): ReviewInterface
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * Get Source
     *
     * @return string|null
     */
    public function getSource(): ?string
    {
        return $this->_get(self::SOURCE);
    }

    /**
     * Set Source
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setSource(?string $value): ReviewInterface
    {
        return $this->setData(self::SOURCE, $value);
    }

    /**
     * Get Source ID
     *
     * @return string|null
     */
    public function getSourceId(): ?string
    {
        return $this->_get(self::SOURCE_ID);
    }

    /**
     * Set Source ID
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setSourceId(?string $value): ReviewInterface
    {
        return $this->setData(self::SOURCE_ID, $value);
    }

    /**
     * Get Updated At
     *
     * @return string|null
     */
    public function getUpdatedAt(): ?string
    {
        return $this->_get(self::UPDATED_AT);
    }

    /**
     * Set Updated At
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setUpdatedAt(?string $value): ReviewInterface
    {
        return $this->setData(self::UPDATED_AT, $value);
    }

    /**
     * Get Parent ID
     *
     * @return string|null
     */
    public function getParentId(): ?string
    {
        return $this->_get(self::PARENT_ID);
    }

    /**
     * Set Parent ID
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setParentId(?string $value): ReviewInterface
    {
        return $this->setData(self::PARENT_ID, $value);
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
     * @return ReviewInterface
     */
    public function setLevel(?string $value): ReviewInterface
    {
        return $this->setData(self::LEVEL, $value);
    }

    /**
     * Get Path
     *
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->_get(self::PATH);
    }

    /**
     * Set Path
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setPath(?string $value): ReviewInterface
    {
        return $this->setData(self::PATH, $value);
    }

    /**
     * Get Rating
     *
     * @return string|null
     */
    public function getRating(): ?string
    {
        return $this->_get(self::RATING);
    }

    /**
     * Set Rating
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setRating(?string $value): ReviewInterface
    {
        return $this->setData(self::RATING, $value);
    }

    /**
     * Get Detail
     *
     * @return string|null
     */
    public function getDetail(): ?string
    {
        return $this->_get(self::DETAIL);
    }

    /**
     * Set Detail
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setDetail(?string $value): ReviewInterface
    {
        return $this->setData(self::DETAIL, $value);
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
     * @return ReviewInterface
     */
    public function setTitle(?string $value): ReviewInterface
    {
        return $this->setData(self::TITLE, $value);
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
     * @return ReviewInterface
     */
    public function setNickname(?string $value): ReviewInterface
    {
        return $this->setData(self::NICKNAME, $value);
    }

    /**
     * Get Rating Votes
     *
     * @return \Magento\Framework\DataObject|null
     */
    public function getRatingVotes()
    {
        return $this->_get(self::RATING_VOTES);
    }

    /**
     * Set Rating Votes
     *
     * @param \Magento\Framework\DataObject|null $value
     * @return ReviewInterface
     */
    public function setRatingVotes($value): ReviewInterface
    {
        return $this->setData(self::RATING_VOTES, $value);
    }

    /**
     * Get Rating Summary
     *
     * @return \Magento\Framework\DataObject|null
     */
    public function getRatingSummary()
    {
        return $this->_get(self::RATING_SUMMARY);
    }

    /**
     * Set Rating Summary
     *
     * @param \Magento\Framework\DataObject|null $value
     * @return ReviewInterface
     */
    public function setRatingSummary($value): ReviewInterface
    {
        return $this->setData(self::RATING_SUMMARY, $value);
    }

    /**
     * Get Review Reply Data
     *
     * @return \Magento\Framework\DataObject|null
     */
    public function getReplyData()
    {
        return $this->_get(self::REPLY_DATA);
    }

    /**
     * Set Review Reply Data
     *
     * @param \Magento\Framework\DataObject|null $value
     * @return ReviewInterface
     */
    public function setReplyData($value): ReviewInterface
    {
        return $this->setData(self::REPLY_DATA, $value);
    }

    /**
     * Get Entity ID
     *
     * @return int|null
     */
    public function getId()
    {
        return $this->_get(self::ENTITY_ID);
    }

    /**
     * Get Review is Approved
     *
     * @return bool|null
     */
    public function isApproved(): ?bool
    {
        return $this->_get('is_approved');
    }

    /**
     * Get Rating Data
     *
     * @return array|null
     */
    public function getRatingsData(): ?array
    {
        return $this->_get('ratings_data');
    }

    /**
     * Get Author Info
     *
     * @return string|null
     */
    public function getAuthorInfo(): ?string
    {
        return $this->_get('author_info');
    }

    /**
     * Set Entity Primary Key Value
     *
     * @param int|null $value
     * @return ReviewInterface
     */
    public function setEntityPkValue(?int $value): ReviewInterface
    {
        return $this->setData('entity_pk_value', $value);
    }

    /**
     * Set Customer ID
     *
     * @param int|null $value
     * @return ReviewInterface
     */
    public function setCustomerId(?int $value): ReviewInterface
    {
        return $this->setData('customer_id', $value);
    }

    /**
     * Set Status ID
     *
     * @param int $value
     * @return ReviewInterface
     */
    public function setStatusId(int $value): ReviewInterface
    {
        return $this->setData(self::STATUS_ID, $value);
    }

    /**
     * Set Store ID
     *
     * @param int $value
     * @return ReviewInterface
     */
    public function setStoreId(int $value): ReviewInterface
    {
        return $this->setData('store_id', $value);
    }

    /**
     * Set Stores
     *
     * @param array|null $value
     * @return ReviewInterface
     */
    public function setStores(?array $value): ReviewInterface
    {
        return $this->setData('stores', $value);
    }

    /**
     * Get Logo Image Path
     *
     * @return string|null
     */
    public function getLogoImage(): ?string
    {
        return $this->_get(self::LOG_IMAGE);
    }

    /**
     * Set Logo Image Path
     *
     * @param string|null $value
     * @return ReviewInterface
     */
    public function setLogoImage(?string $value): ReviewInterface
    {
        return $this->setData(self::LOG_IMAGE, $value);
    }
}
