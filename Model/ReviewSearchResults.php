<?php

declare(strict_types=1);

namespace Local\Comments\Model;

use Magento\Framework\Api\SearchResults;
use Local\Comments\Api\Data\ReviewSearchResultsInterface;

class ReviewSearchResults extends SearchResults implements ReviewSearchResultsInterface
{
}
