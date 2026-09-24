<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Provider;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Patch;
use DR\Review\ApiPlatform\Provider\DeleteCommentProvider;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[CoversClass(DeleteCommentProvider::class)]
class DeleteCommentProviderTest extends AbstractTestCase
{
    private CommentRepository&MockObject $commentRepository;
    private DeleteCommentProvider $provider;
    private Comment $comment;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->comment = new Comment()->setId(123);
        $this->user = new User()->setId(10);
        $this->provider = $this->createProvider(null, false);
    }

    public function testProvidesVisibleComment(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($this->comment);
        $this->provider = $this->createProvider($this->user, true);

        self::assertSame($this->comment, $this->provider->provide(new Delete(), ['id' => '123']));
    }

    public function testMissingCommentReturnsNotFound(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->expectException(NotFoundHttpException::class);
        $this->provider->provide(new Delete(), ['id' => '123']);
    }

    public function testHiddenDraftReturnsNotFound(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($this->comment);
        $userProvider = $this->userStub($this->user);
        $visibility = $this->createMock(CommentVisibility::class);
        $visibility->expects($this->once())->method('isVisible')->with($this->comment, $this->user)->willReturn(false);
        $this->provider = new DeleteCommentProvider($this->commentRepository, $userProvider, $visibility);

        $this->expectException(NotFoundHttpException::class);
        $this->provider->provide(new Delete(), ['id' => '123']);
    }

    public function testUnauthenticatedPassesSecurity(): void
    {
        $this->commentRepository->expects($this->once())->method('find')->with(123)->willReturn($this->comment);
        $this->provider = $this->createProvider(null, false);

        self::assertSame($this->comment, $this->provider->provide(new Delete(), ['id' => '123']));
    }

    public function testRejectsNonDeleteOperation(): void
    {
        $this->commentRepository->expects($this->never())->method('find');
        $this->expectException(RuntimeException::class);

        $this->provider->provide(new Patch(), ['id' => '123']);
    }

    private function createProvider(?User $user, bool $visible): DeleteCommentProvider
    {
        $userProvider = $this->userStub($user);
        $visibility = $this->visibilityStub($visible);

        return new DeleteCommentProvider($this->commentRepository, $userProvider, $visibility);
    }

    private function userStub(?User $user): UserEntityProvider
    {
        $userProvider = static::createStub(UserEntityProvider::class);
        $userProvider->method('getUser')->willReturn($user);

        return $userProvider;
    }

    private function visibilityStub(bool $visible): CommentVisibility
    {
        $stub = static::createStub(CommentVisibility::class);
        $stub->method('isVisible')->willReturn($visible);

        return $stub;
    }
}
