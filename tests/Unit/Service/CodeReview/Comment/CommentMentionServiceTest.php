<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\UserMentionRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CommentMentionService::class)]
class CommentMentionServiceTest extends AbstractTestCase
{
    private UserRepository&MockObject        $userRepository;
    private UserMentionRepository&MockObject $mentionRepository;
    private CommentMentionService            $mentionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository    = $this->createMock(UserRepository::class);
        $this->mentionRepository = $this->createMock(UserMentionRepository::class);
        $this->mentionService    = new CommentMentionService($this->userRepository, $this->mentionRepository);
    }

    public function testUpdateMentionsCommentWithoutMentions(): void
    {
        $comment = new Comment();
        $comment->setMessage('foobar');

        $this->mentionRepository->expects($this->once())->method('saveAll')->with($comment, []);

        $this->mentionService->updateMentions($comment);
    }

    public function testUpdateMentions(): void
    {
        $comment = new Comment();
        $comment->setMessage('foobar @user:123[Holmes] foobar');

        $reply = new CommentReply();
        $reply->setMessage('foobar @user:456[Watson] @user:123[Holmes] foobar');
        $comment->getReplies()->add($reply);

        $userA = new User();
        $userA->setId(123);
        $userB = new User();
        $userB->setId(456);

        $this->userRepository->expects($this->once())->method('findAll')->willReturn([$userA, $userB]);
        $this->mentionRepository->expects($this->once())
            ->method('saveAll')
            ->with(
                $comment,
                self::callback(static function ($mentions) {
                    static::assertCount(2, $mentions);

                    return true;
                })
            );

        $this->mentionService->updateMentions($comment);
    }

    public function testGetMentionedUsersNoMentionNoUser(): void
    {
        static::assertSame([], $this->mentionService->getMentionedUsers('foobar'));
    }

    public function testGetMentionedUsersMentionedUserShouldMatch(): void
    {
        $user = new User();
        $user->setId(123);

        $this->userRepository->expects($this->once())->method('findAll')->willReturn([$user]);

        static::assertSame(
            ['@user:123[Sherlock holmes]' => $user],
            $this->mentionService->getMentionedUsers('foobar @user:123[Sherlock holmes] foobar')
        );
    }

    public function testGetMentionedUsersUnknownUserShouldNotMatch(): void
    {
        $this->userRepository->expects($this->once())->method('findAll')->willReturn([]);

        static::assertSame([], $this->mentionService->getMentionedUsers('foobar @user:123[Sherlock holmes] foobar'));
    }

    public function testReplaceMentionedUsers(): void
    {
        $user = new User();
        $user->setId(123);
        $user->setName('Sherlock Holmes');
        $user->setEmail('sherlock@example.com');

        $actual = $this->mentionService->replaceMentionedUsers(
            'foobar @user:123[Sherlock Holmes] foobar @user:456[unknown] foobar',
            ['@user:123[Sherlock Holmes]' => $user]
        );
        static::assertSame('foobar [@Sherlock Holmes](mailto:sherlock@example.com) foobar @user:456[unknown] foobar', $actual);
    }
}
