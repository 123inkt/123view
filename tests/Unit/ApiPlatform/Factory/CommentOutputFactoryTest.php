<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Factory;

use DR\Review\ApiPlatform\Factory\CommentOutputFactory;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentOutputFactory::class)]
class CommentOutputFactoryTest extends AbstractTestCase
{
    private CommentOutputFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new CommentOutputFactory();
    }

    public function testCreateMapsCommentFields(): void
    {
        $user    = new User()->setId(10);
        $review  = new CodeReview()->setId(20);
        $comment = new Comment()
            ->setId(123)
            ->setUser($user)
            ->setReview($review)
            ->setMessage('Comment text')
            ->setFilePath('src/Foo.php')
            ->setLineReference(new LineReference(null, 'src/Foo.php', 40, 2, 42, 'abc123'))
            ->setState(CommentStateEnum::Resolved)
            ->setTag(CommentTagEnum::Suggestion)
            ->setCreateTimestamp(1234567890)
            ->setUpdateTimestamp(1234567891);

        $output = $this->factory->create($comment);

        static::assertSame(123, $output->id);
        static::assertSame(10, $output->userId);
        static::assertSame(20, $output->reviewId);
        static::assertSame('Comment text', $output->message);
        static::assertSame('src/Foo.php', $output->filepath);
        static::assertSame(42, $output->line);
        static::assertSame('abc123', $output->sha);
        static::assertSame('resolved', $output->state);
        static::assertSame('suggestion', $output->tag);
        static::assertSame(1234567890, $output->createTimestamp);
        static::assertSame(1234567891, $output->updateTimestamp);
    }

    public function testCreateMapsNullableFields(): void
    {
        $comment = new Comment()
            ->setId(123)
            ->setUser(new User()->setId(10))
            ->setReview(new CodeReview()->setId(20))
            ->setMessage('Legacy comment')
            ->setFilePath('src/Legacy.php')
            ->setLineReference(new LineReference(null, 'src/Legacy.php', 7, 1, 8))
            ->setTag(null)
            ->setCreateTimestamp(1000)
            ->setUpdateTimestamp(2000);

        $output = $this->factory->create($comment);

        static::assertNull($output->sha);
        static::assertNull($output->tag);
    }
}
