<?php
declare(strict_types=1);

namespace DR\Review\Model\Search;

enum SearchResultLineTypeEnum: string
{
    case Context = 'context';
    case Match = 'match';
}
