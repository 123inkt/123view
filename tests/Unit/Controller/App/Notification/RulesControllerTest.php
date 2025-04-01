<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Notification\RulesController;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends AbstractControllerTestCase<RulesController>
 */
#[CoversClass(RulesController::class)]
class RulesControllerTest extends AbstractControllerTestCase
{
    public function testInvokeWithoutUser(): void
    {
        // invoke controller
        $this->expectGetUser(null);
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied');
        ($this->controller)();
    }

    public function testInvoke(): void
    {
        $rule = new Rule();
        $user = new User();
        $user->addRule($rule);

        $this->expectGetUser($user);

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
