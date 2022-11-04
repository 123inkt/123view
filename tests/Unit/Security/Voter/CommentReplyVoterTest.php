<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Security\Voter;

use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Security\Voter\CommentReplyVoter;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Security\Voter\CommentReplyVoter
 */
class CommentReplyVoterTest extends AbstractTestCase
{
    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupports(): void
    {
        $user    = new User();
        $comment = new CommentReply();
        $comment->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn($user);

        $voter = new CommentReplyVoter();
        static::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $comment, [CommentReplyVoter::EDIT]));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsInvalidAttribute(): void
    {
        $user    = new User();
        $comment = new CommentReply();
        $comment->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::never())->method('getUser');

        $voter = new CommentReplyVoter();
        static::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, $comment, ['foobar']));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsInvalidSubject(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::never())->method('getUser');

        $voter = new CommentReplyVoter();
        static::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, false, [CommentReplyVoter::EDIT]));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsAbsentUser(): void
    {
        $user = new User();
        $user->setId(1);
        $comment = new CommentReply();
        $comment->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn(null);

        $voter = new CommentReplyVoter();
        static::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $comment, [CommentReplyVoter::EDIT]));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsNotRuleOwner(): void
    {
        $user = new User();
        $user->setId(1);
        $comment = new CommentReply();
        $comment->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn((new User())->setId(5));

        $voter = new CommentReplyVoter();
        static::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $comment, [CommentReplyVoter::EDIT]));
    }
}
