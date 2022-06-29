<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App;

use DR\GitCommitNotification\Controller\App\RulesController;
use DR\GitCommitNotification\Entity\Rule;
use DR\GitCommitNotification\Entity\User;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\ViewModel\App\RulesViewModel;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\RulesController
 * @covers ::__construct
 */
class RulesControllerTest extends AbstractTestCase
{
    private User $user;

    protected function setUp(): void
    {
        $this->user = new User();
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeWithoutUser(): void
    {
        $controller = new RulesController(null);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied');
        $controller();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeWithUser(): void
    {
        $rule = new Rule();
        $this->user->addRule($rule);
        $controller = new RulesController($this->user);

        $result = ($controller)();
        static::assertArrayHasKey('rulesModel', $result);
        static::assertEquals((new RulesViewModel())->setRules([$rule]), $result['rulesModel']);
    }
}
