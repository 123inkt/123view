<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Model\Webhook\Gitlab;

use DR\Review\Model\Api\Gitlab\Project;
use DR\Review\Model\Webhook\Gitlab\MergeRequestEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MergeRequestEvent::class)]
class MergeRequestEventTest extends TestCase
{
    public function testToString(): void
    {
        $project       = new Project();
        $project->name = 'review-repository';

        $event               = new MergeRequestEvent();
        $event->project      = $project;
        $event->action       = 'approved';
        $event->sourceBranch = 'feature';

        static::assertSame('MergeRequestEvent(project: review-repository, action: approved, sourceBranch: feature)', (string)$event);
    }
}
