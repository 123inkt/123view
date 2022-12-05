<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security;

use DR\Review\Entity\User\User;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\UserChecker;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\Review\Security\UserChecker
 * @covers ::__construct
 */
class UserCheckerTest extends AbstractTestCase
{
    private TranslatorInterface&MockObject $translator;
    private UserChecker                    $checker;

    public function setUp(): void
    {
        parent::setUp();
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->checker    = new UserChecker($this->translator);
    }

    /**
     * @covers ::checkPreAuth
     */
    public function testCheckPreAuth(): void
    {
        $userA = new User();
        $userA->setRoles([]);
        $userB = new User();
        $userB->setRoles([Roles::ROLE_BANNED]);

        // sanity check, userA should be allowed
        $this->checker->checkPreAuth($userA);

        $this->translator->expects(self::once())->method('trans')->with('user.account.suspended')->willReturn('account suspended');

        // userB should be disallowed
        $this->expectException(CustomUserMessageAuthenticationException::class);
        $this->expectExceptionMessage('account suspended');
        $this->checker->checkPreAuth($userB);
    }
}
