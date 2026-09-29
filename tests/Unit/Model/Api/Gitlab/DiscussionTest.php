<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Model\Api\Gitlab;

use DR\Review\Model\Api\Gitlab\Discussion;
use DR\Review\Model\Api\Gitlab\Note;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Discussion::class)]
class DiscussionTest extends AbstractTestCase
{
    public function testAddGetAndRemoveNotes(): void
    {
        $noteA     = new Note();
        $noteA->id = 1;
        $noteB     = new Note();
        $noteB->id = 2;
        $discussion = new Discussion();

        $discussion->addNote($noteA);
        $discussion->addNote($noteB);

        static::assertSame($noteA, $discussion->getNote(0));
        static::assertSame($noteB, $discussion->getNote(1));
        static::assertNull($discussion->getNote(2));
        static::assertSame([$noteA, $noteB], $discussion->getNotes());

        $discussion->removeNote($noteA);

        static::assertSame([$noteB], $discussion->getNotes());
    }
}
