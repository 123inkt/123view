<?php
declare(strict_types=1);

namespace DR\Review\Model\SummaryNote;

readonly class MergeRequestSummaryNote
{
    public function __construct(public SummaryNoteStatus $status, public string $title, public string $html, public string $url)
    {
    }
}
