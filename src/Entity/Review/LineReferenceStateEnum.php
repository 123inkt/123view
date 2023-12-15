<?php

declare(strict_types=1);

namespace DR\Review\Entity\Review;

enum LineReferenceStateEnum: string
{
    case Modified = 'M';
    case Unmodified = 'U';
    case Added = 'A';
    case Deleted = 'D';
    case Unknown = '?';
}
