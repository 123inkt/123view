<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview\Comment;

use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Repository\Config\UserRepository;
use DR\GitCommitNotification\Service\CodeReview\Comment\CommentMentionService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeReview\Comment\CommentMentionService
 * @covers ::__construct
 */
class CommentMentionServiceTest extends AbstractTestCase
{
    private UserRepository&MockObject $userRepository;
    private CommentMentionService     $mentionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->mentionService = new CommentMentionService($this->userRepository);
    }

    /**
     * @covers ::getMentionedUsers
     */
    public function testGetMentionedUsersNoMentionNoUser(): void
    {
        static::assertSame([], $this->mentionService->getMentionedUsers('foobar'));
    }

    /**
     * @covers ::getMentionedUsers
     */
    public function testGetMentionedUsersMentionedUserShouldMatch(): void
    {
        $user = new User();
        $user->setId(123);

        $this->userRepository->expects(self::once())->method('findAll')->willReturn([$user]);

        static::assertSame(
            ['@user:123[Sherlock holmes]' => $user],
            $this->mentionService->getMentionedUsers('foobar @user:123[Sherlock holmes] foobar')
        );
    }

    /**
     * @covers ::getMentionedUsers
     */
    public function testGetMentionedUsersUnknownUserShouldNotMatch(): void
    {
        $this->userRepository->expects(self::once())->method('findAll')->willReturn([]);

        static::assertSame([], $this->mentionService->getMentionedUsers('foobar @user:123[Sherlock holmes] foobar'));
    }
}
