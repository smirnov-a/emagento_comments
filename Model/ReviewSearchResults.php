<?php

declare(strict_types=1);

namespace Emagento\Comments\Model;

use Magento\Framework\Api\SearchResults;
use Emagento\Comments\Api\Data\ReviewSearchResultsInterface;

class ReviewSearchResults extends SearchResults implements ReviewSearchResultsInterface
{
}
