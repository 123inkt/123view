<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\DraftCommentExtension;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(DraftCommentExtension::class)]
class DraftCommentExtensionTest extends AbstractTestCase
{
    public function testGetDraftCountReturnsZeroWhenNotLoggedIn(): void
    {
        $userProvider = $this->createMock(UserEntityProvider::class);
        $userProvider->method('getUser')->willReturn(null);

        $repository = $this->createMock(CommentRepository::class);
        $repository->expects($this->never())->method('countDraftsByUser');

        $extension = new DraftCommentExtension($userProvider, $repository);

        static::assertSame(0, $extension->getDraftCount());
    }

    public function testGetDraftCountReturnsCountForLoggedInUser(): void
    {
        $user = $this->createMock(User::class);

        $userProvider = $this->createMock(UserEntityProvider::class);
        $userProvider->method('getUser')->willReturn($user);

        $repository = $this->createMock(CommentRepository::class);
        $repository->expects($this->once())->method('countDraftsByUser')->with($user)->willReturn(5);

        $extension = new DraftCommentExtension($userProvider, $repository);

        static::assertSame(5, $extension->getDraftCount());
    }

    public function testGetDraftCountIsCached(): void
    {
        $user = $this->createMock(User::class);

        $userProvider = $this->createMock(UserEntityProvider::class);
        $userProvider->method('getUser')->willReturn($user);

        $repository = $this->createMock(CommentRepository::class);
        // Called only ONCE despite two calls to getDraftCount()
        $repository->expects($this->once())->method('countDraftsByUser')->willReturn(3);

        $extension = new DraftCommentExtension($userProvider, $repository);

        static::assertSame(3, $extension->getDraftCount());
        static::assertSame(3, $extension->getDraftCount()); // second call uses cache
    }
}
