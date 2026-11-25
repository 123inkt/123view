<?php
declare(strict_types=1);

namespace DR\Review\Model\SummaryNote;

enum SummaryNoteStatus: string
{
    case Success = 'success';
    case Warning = 'warning';
    case Unknown = 'unknown';
}
