<?php

declare(strict_types=1);

namespace BAT\DevicePersonalisation\Model;

use BAT\DevicePersonalisation\Api\Data\PsnRestrictedWordsSearchResultsInterface;
use Magento\Framework\Api\SearchResults;

class PsnRestrictedWordsSearchResults extends SearchResults implements PsnRestrictedWordsSearchResultsInterface
{
}
