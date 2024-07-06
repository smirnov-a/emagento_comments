<?php

namespace Emagento\Comments\Model\Cache\Type;

use Magento\Framework\App\Cache\Type\FrontendPool;
use Magento\Framework\Cache\Frontend\Decorator\TagScope;

class CacheType extends TagScope
{
    public const TYPE_IDENTIFIER = 'emagento_comments_cache_type_id';
    public const CACHE_TAG = 'EMAGENTO_COMMENTS_CACHE_TYPE_TAG';

    /**
     * @param FrontendPool $cacheFrontendPool
     */
    public function __construct(FrontendPool $cacheFrontendPool)
    {
        parent::__construct(
            $cacheFrontendPool->get(self::TYPE_IDENTIFIER),
            self::CACHE_TAG
        );
    }
}
