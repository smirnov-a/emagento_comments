<?php

namespace Emagento\Comments\Helper;

class Constants
{
    public const PAGE = 1;
    public const LIMIT = 10;
    public const XML_CONFIG_PREFIX_PATH = 'local_comments';
    public const REVIEW_ENTITY_TYPE_BY_STORE = 'store';
    public const REVIEW_ENTITY_TYPE_BY_PRODUCT = 'product';
    public const LOCAL_COMMENT_EDIT_PATH = '/local_comments/comment/edit/';
    public const LOCAL_COMMENT_GRID_PATH = 'local_comments/grid/';
    public const LOCAL_COMMENT_REVIEW_LOAD_PATH = 'local_comments/reviews/load';
    public const TITLE_DEFAULT = '_auto_';
    public const TYPE_LOCAL = 'local';
    public const TYPE_FLAMP = 'flamp';
    public const TYPE_YANDEX = 'yandex';
    public const TYPE_ALL = 'all';
    public const PATH_COMMENTS = 'emagento/';
    public const RATING_VERY_BAD = 'very bad';
    public const RATING_BAD = 'bad';
    public const RATING_MEDIUM = 'medium';
    public const RATING_GOOD = 'good';
    public const RATING_VERY_GOOD = 'very good';
    public const RATING_VALUES = [
        1 => self::RATING_VERY_BAD,
        2 => self::RATING_BAD,
        3 => self::RATING_MEDIUM,
        4 => self::RATING_GOOD,
        5 => self::RATING_VERY_GOOD,
    ];
}
