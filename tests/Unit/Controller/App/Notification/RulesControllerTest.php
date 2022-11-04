<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Notification;

use DR\GitCommitNotification\Controller\App\Notification\RulesController;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Notification\RulesController
 */
class RulesControllerTest extends AbstractControllerTestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvokeWithoutUser(): void
    {
        // invoke controller
        $this->expectUser(null);
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied');
        ($this->controller)();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $rule = new Rule();
        $user = new User();
        $user->addRule($rule);

        $this->expectUser($user);

        // invoke controller
        $result = ($this->controller)();

        static::assertArrayHasKey('rulesModel', $result);
        static::assertSame([$rule], iterator_to_array($result['rulesModel']->getRules()));
    }

    public function getController(): AbstractController
    {
        return new RulesController();
    }
}
