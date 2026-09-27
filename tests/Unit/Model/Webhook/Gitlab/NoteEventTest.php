<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Model\Webhook\Gitlab;

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
        static::assertSame(456, $event->mergeRequestIId);
        static::assertSame('feature', $event->sourceBranch);
        static::assertSame('master', $event->targetBranch);
        static::assertSame('discussion', $event->discussionId);
        static::assertSame('Please update this line.', $event->note);
        static::assertSame('MergeRequest', $event->noteType);
        static::assertSame('create', $event->action);
        static::assertSame(42, $event->user->id);
        static::assertSame('head-sha', $event->position->headSha);
        static::assertSame('new.php', $event->position->newPath);
        static::assertSame(12, $event->position->newLine);
    }
}
