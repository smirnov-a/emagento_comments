<?php

namespace Local\Comments\Api\Data;

interface ReviewInterface
{
    const REVIEW_ID       = 'review_id';
    const CREATED_AT      = 'created_at';
    const ENTITY_ID       = 'entity_id';
    const ENTITY_PK_VALUE = 'entity_pk_value';
    const STATUS_ID       = 'status_id';
    const SOURCE          = 'source';
    const SOURCE_ID       = 'source_id';
    const UPDATED_AT      = 'updated_at';
    const PARENT_ID       = 'parent_id';
    const LEVEL           = 'level';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get identifier
     *
     * @return string
     */
    public function getCreatedAt();

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @return int
     */
    public function getEntityPkValue();

    /**
     * @return int
     */
    public function getStatusId();

    /**
     * @return string|null
     */
    public function getSource();

    /**
     * @return string|null
     */
    public function getSourceId();

    /**
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * @return int|null
     */
    public function getParentId();

    /**
     * @return int|null
     */
    public function getLevel();

    /**
     * @param int $id
     * @return \Local\Comments\Api\Data\ReviewInterface
     */
    public function setId($id);

    /**
     * @param string $createdAt
     * @return \Local\Comments\Api\Data\ReviewInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * @param int $entityId
     * @return \Local\Comments\Api\Data\ReviewInterface
     */
    public function setEntityId($entityId);

    /**
     * @param int $entityPkValue
     * @return \Local\Comments\Api\Data\ReviewInterface
     */
    public function setEntityPkValue($entityPkValue);

    /**
     * @param int $statusId
     * @return \Local\Comments\Api\Data\ReviewInterface
     */
    public function setStatusId($statusId);

    /**
     * @param string $source
     * @return \Local\Comments\Api\Data\ReviewInterface
     */
    public function setSource($source);

    /**
     * @param string $sourceId
     * @return \Local\Comments\Api\Data\ReviewInterface
     */
    public function setSourceId($sourceId);

    /**
     * @param string $updatedAt
     * @return \Local\Comments\Api\Data\ReviewInterface
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @param int $parentId
     * @return \Local\Comments\Api\Data\ReviewInterface
     */
    public function setParentId($parentId);

    /**
     * @param int $level
     * @return \Local\Comments\Api\Data\ReviewInterface
     */
    public function setLevel($level);
}
