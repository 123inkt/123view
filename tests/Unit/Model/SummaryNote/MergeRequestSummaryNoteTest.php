<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Model\SummaryNote;

use DR\Review\Model\SummaryNote\MergeRequestSummaryNote;
use DR\Review\Model\SummaryNote\SummaryNoteStatus;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MergeRequestSummaryNote::class)]
class MergeRequestSummaryNoteTest extends AbstractTestCase
{
    public function testConstruct(): void
    {
        $summaryNote = new MergeRequestSummaryNote(SummaryNoteStatus::Warning, 'title', '<p>html</p>', 'https://gitlab/mr#note_1');

        static::assertSame(SummaryNoteStatus::Warning, $summaryNote->status);
        static::assertSame('title', $summaryNote->title);
        static::assertSame('<p>html</p>', $summaryNote->html);
        static::assertSame('https://gitlab/mr#note_1', $summaryNote->url);
    }
}
