<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Model\Webhook\Gitlab;

use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

#[CoversClass(NoteEvent::class)]
class NoteEventTest extends TestCase
{
    public function testToString(): void
    {
        $event                                = new NoteEvent();
        $event->id                            = 789;
        $event->mergeRequest                  = new MergeRequest();
        $event->mergeRequest->mergeRequestIId = 456;
        $event->noteType                      = 'MergeRequest';
        $event->action                        = 'create';

        static::assertSame('NoteEvent(id: 789, mergeRequestIID 456, type: MergeRequest, action: create)', (string)$event);
    }

    public function testDenormalizeGitlabNotePayload(): void
    {
        $metadataFactory = new ClassMetadataFactory(new AttributeLoader());
        $normalizer      = new ObjectNormalizer(
            $metadataFactory,
            new MetadataAwareNameConverter($metadataFactory),
            null,
            new ReflectionExtractor()
        );
        $event           = new Serializer([$normalizer])->denormalize(
            [
                'project_id'        => 123,
                'merge_request'     => [
                    'iid'           => 456,
                    'source_branch' => 'feature',
                    'target_branch' => 'master',
                ],
                'object_attributes' => [
                    'id'            => 789,
                    'discussion_id' => 'discussion',
                    'note'          => 'Please update this line.',
                    'noteable_type' => 'MergeRequest',
                    'action'        => 'create',
                    'resolved_at'   => '2026-09-29T12:00:00.000Z',
                    'position'      => [
                        'position_type' => 'text',
                        'base_sha'      => 'base-sha',
                        'head_sha'      => 'head-sha',
                        'start_sha'     => 'start-sha',
                        'old_path'      => 'old.php',
                        'new_path'      => 'new.php',
                        'old_line'      => 10,
                        'new_line'      => 12,
                    ],
                ],
                'user'              => [
                    'id'         => 42,
                    'name'       => 'User',
                    'username'   => 'user',
                    'avatar_url' => 'https://example.com/avatar',
                    'email'      => 'user@example.com',
                ],
            ],
            NoteEvent::class
        );

        static::assertInstanceOf(NoteEvent::class, $event);
        static::assertSame(123, $event->projectId);
        static::assertNotNull($event->mergeRequest);
        static::assertSame(456, $event->mergeRequest->mergeRequestIId);
        static::assertSame('feature', $event->mergeRequest->sourceBranch);
        static::assertSame('master', $event->mergeRequest->targetBranch);
        static::assertSame('discussion', $event->discussionId);
        static::assertSame('Please update this line.', $event->note);
        static::assertSame('MergeRequest', $event->noteType);
        static::assertSame('create', $event->action);
        static::assertSame('2026-09-29T12:00:00.000Z', $event->resolvedAt);
        static::assertSame(42, $event->user->id);
        static::assertNotNull($event->position);
        static::assertSame('head-sha', $event->position->headSha);
        static::assertSame('new.php', $event->position->newPath);
        static::assertSame(12, $event->position->newLine);
    }
}
