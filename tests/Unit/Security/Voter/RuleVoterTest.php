<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\Voter;

use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\User\User;
use DR\Review\Security\Voter\RuleVoter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

#[CoversClass(RuleVoter::class)]
class RuleVoterTest extends AbstractTestCase
{
    public function testSupports(): void
    {
        $user = (new User())->setId(789);
        $rule = new Rule();
        $rule->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn($user);

        $voter = new RuleVoter();
        static::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $rule, [RuleVoter::EDIT]));
    }

    public function testSupportsInvalidAttribute(): void
    {
        $user = new User();
        $rule = new Rule();
        $rule->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::never())->method('getUser');

        $voter = new RuleVoter();
        static::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, $rule, ['foobar']));
    }

    public function testSupportsInvalidSubject(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::never())->method('getUser');

        $voter = new RuleVoter();
        static::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, false, [RuleVoter::EDIT]));
    }

    public function testSupportsAbsentUser(): void
    {
        $user = new User();
        $user->setId(1);
        $rule = new Rule();
        $rule->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn(null);

        $voter = new RuleVoter();
        static::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $rule, [RuleVoter::EDIT]));
    }

    public function testSupportsNotRuleOwner(): void
    {
        $user = new User();
        $user->setId(1);
        $rule = new Rule();
        $rule->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects($this->once())->method('getUser')->willReturn((new User())->setId(5));

        $voter = new RuleVoter();
        static::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $rule, [RuleVoter::EDIT]));
    }
}
