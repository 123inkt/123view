<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App;

use DR\GitCommitNotification\Controller\App\RulesController;
use DR\GitCommitNotification\Entity\Rule;
use DR\GitCommitNotification\Entity\User;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\RulesController
 * @covers ::__construct
 */
class RulesControllerTestCase extends AbstractTestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvokeWithoutUser(): void
    {
        // invoke controller
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied');
        (new RulesController(null))();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $rule = new Rule();
        $user = new User();
        $user->addRule($rule);

        // invoke controller
        $result = (new RulesController($user))();

        static::assertArrayHasKey('rulesModel', $result);
        static::assertSame([$rule], $result['rulesModel']->getRules());
    }
}
