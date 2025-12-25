<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\Voter;

use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\User\User;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

#[CoversClass(CommentReplyVoter::class)]
class CommentReplyVoterTest extends AbstractTestCase
{
    public function testSupports(): void
    {
        $user    = (new User())->setId(789);
        $comment = new CommentReply();
        $comment->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $voter = new CommentReplyVoter();
        static::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $comment, [CommentReplyVoter::EDIT]));
    }

    public function testSupportsInvalidAttribute(): void
    {
        $user    = new User();
        $comment = new CommentReply();
        $comment->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->never())->method('getUser');

        $voter = new CommentReplyVoter();
        static::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, $comment, ['foobar']));
    }

    public function testSupportsInvalidSubject(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->never())->method('getUser');

        $voter = new CommentReplyVoter();
        static::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, false, [CommentReplyVoter::EDIT]));
    }

    public function testSupportsAbsentUser(): void
    {
        $user = new User();
        $user->setId(1);
        $comment = new CommentReply();
        $comment->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn(null);

        $voter = new CommentReplyVoter();
        static::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $comment, [CommentReplyVoter::EDIT]));
    }

    public function testSupportsNotRuleOwner(): void
    {
        $user = new User();
        $user->setId(1);
        $comment = new CommentReply();
        $comment->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn((new User())->setId(5));

        $voter = new CommentReplyVoter();
        static::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $comment, [CommentReplyVoter::EDIT]));
    }
}
