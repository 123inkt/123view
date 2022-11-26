<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Security\Voter;

use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Security\Voter\RuleVoter;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Security\Voter\RuleVoter
 */
class RuleVoterTest extends AbstractTestCase
{
    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupports(): void
    {
        $user = new User();
        $rule = new Rule();
        $rule->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn($user);

        $voter = new RuleVoter();
        static::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $rule, [RuleVoter::EDIT]));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
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

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsInvalidSubject(): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::never())->method('getUser');

        $voter = new RuleVoter();
        static::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, false, [RuleVoter::EDIT]));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsAbsentUser(): void
    {
        $user = new User();
        $user->setId(1);
        $rule = new Rule();
        $rule->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn(null);

        $voter = new RuleVoter();
        static::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $rule, [RuleVoter::EDIT]));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsNotRuleOwner(): void
    {
        $user = new User();
        $user->setId(1);
        $rule = new Rule();
        $rule->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn((new User())->setId(5));

        $voter = new RuleVoter();
        static::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $rule, [RuleVoter::EDIT]));
    }
}
