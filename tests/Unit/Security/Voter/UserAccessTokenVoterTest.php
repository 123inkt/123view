<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\Voter;

use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Security\Voter\UserAccessTokenVoter;
use DR\Review\Tests\AbstractTestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

/**
 * @coversDefaultClass \DR\Review\Security\Voter\UserAccessTokenVoter
 */
class UserAccessTokenVoterTest extends AbstractTestCase
{
    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupports(): void
    {
        $user = (new User())->setId(789);
        $rule = new UserAccessToken();
        $rule->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn($user);

        $voter = new UserAccessTokenVoter();
        static::assertSame(VoterInterface::ACCESS_GRANTED, $voter->vote($token, $rule, [UserAccessTokenVoter::DELETE]));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsInvalidAttribute(): void
    {
        $user = new User();
        $rule = new UserAccessToken();
        $rule->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::never())->method('getUser');

        $voter = new UserAccessTokenVoter();
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

        $voter = new UserAccessTokenVoter();
        static::assertSame(VoterInterface::ACCESS_ABSTAIN, $voter->vote($token, false, [UserAccessTokenVoter::DELETE]));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsAbsentUser(): void
    {
        $user = new User();
        $user->setId(1);
        $rule = new UserAccessToken();
        $rule->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn(null);

        $voter = new UserAccessTokenVoter();
        static::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $rule, [UserAccessTokenVoter::DELETE]));
    }

    /**
     * @covers ::supports
     * @covers ::voteOnAttribute
     */
    public function testSupportsNotRuleOwner(): void
    {
        $user = new User();
        $user->setId(1);
        $rule = new UserAccessToken();
        $rule->setUser($user);

        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::once())->method('getUser')->willReturn((new User())->setId(5));

        $voter = new UserAccessTokenVoter();
        static::assertSame(VoterInterface::ACCESS_DENIED, $voter->vote($token, $rule, [UserAccessTokenVoter::DELETE]));
    }
}
