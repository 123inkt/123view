<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\User;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\User\GitAccessToken;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitAccessToken::class)]
class GitAccessTokenTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $config = (new ConstraintConfig())->setExcludedMethods(['setGitType', 'getGitType']);
        static::assertAccessorPairs(GitAccessToken::class, $config);
    }

    public function testGitType(): void
    {
        $token = new GitAccessToken();
        static::assertNull($token->getGitType());

        $token->setGitType(RepositoryGitType::GITHUB);
        static::assertSame(RepositoryGitType::GITHUB, $token->getGitType());
    }
}
