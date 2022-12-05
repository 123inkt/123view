<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\Voter;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Tests\AbstractTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @coversDefaultClass \DR\Review\Security\Voter\CommentVoter
 */
class CommentVoterTest extends AbstractTestCase
{
    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupports(): void
    {
        $user    = new User();
        $comment = new Comment();
        $comment->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn($user);

        $voter = new CommentVoter();
        static::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $comment, [CommentVoter::EDIT]));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsInvalidAttribute(): void
    {
        $user    = new User();
        $comment = new Comment();
        $comment->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::never())->method('getUser');

        $voter = new CommentVoter();
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

        $voter = new CommentVoter();
        static::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, false, [CommentVoter::EDIT]));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsAbsentUser(): void
    {
        $user = new User();
        $user->setId(1);
        $comment = new Comment();
        $comment->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn(null);

        $voter = new CommentVoter();
        static::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $comment, [CommentVoter::EDIT]));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsNotRuleOwner(): void
    {
        $user = new User();
        $user->setId(1);
        $comment = new Comment();
        $comment->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn((new User())->setId(5));

        $voter = new CommentVoter();
        static::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $comment, [CommentVoter::EDIT]));
    }
}
