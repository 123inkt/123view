<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\User\User;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentVisibility::class)]
class CommentVisibilityTest extends AbstractTestCase
{
    private CommentVisibility $visibility;

    protected function setUp(): void
    {
        parent::setUp();
        $this->visibility = new CommentVisibility();
    }

    public function testFinalCommentByCurrentUserIsVisible(): void
    {
        $user = new User()->setId(10);

        static::assertTrue($this->visibility->isVisible($this->createComment(CommentTypeEnum::Final, $user), $user));
    }

    public function testFinalCommentByAnotherUserIsVisible(): void
    {
        $author = new User()->setId(10);
        $viewer = new User()->setId(20);

        static::assertTrue($this->visibility->isVisible($this->createComment(CommentTypeEnum::Final, $author), $viewer));
    }

    public function testCurrentUsersDraftIsVisible(): void
    {
        $user = new User()->setId(10);

        static::assertTrue($this->visibility->isVisible($this->createComment(CommentTypeEnum::Draft, $user), $user));
    }

    public function testAnotherUsersDraftIsHidden(): void
    {
        $author = new User()->setId(10);
        $viewer = new User()->setId(20);

        static::assertFalse($this->visibility->isVisible($this->createComment(CommentTypeEnum::Draft, $author), $viewer));
    }

    private function createComment(CommentTypeEnum $type, User $author): Comment
    {
        return new Comment()->setType($type)->setUser($author);
    }
}
