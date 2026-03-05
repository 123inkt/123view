<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Notification\DuplicateRuleController;
use DR\Review\Controller\App\Notification\RuleController;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @extends AbstractControllerTestCase<DuplicateRuleController>
 */
#[CoversClass(DuplicateRuleController::class)]
class DuplicateRuleControllerTest extends AbstractControllerTestCase
{
    private RuleRepository&MockObject $ruleRepository;

    protected function setUp(): void
    {
        $this->ruleRepository = $this->createMock(RuleRepository::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $rule = new Rule()->setName('My Rule')->setActive(true);
        $rule->setId(1);

        $this->ruleRepository->expects($this->once())
            ->method('save')
            ->with(new Rule()->setName('Copy of My Rule')->setActive(false), true)
            ->willReturnCallback(static function (Rule $copy): void { $copy->setId(2); });

        $this->expectGenerateUrl(RuleController::class, ['id' => 2])->willReturn('/app/rules/rule/2');

        $response = ($this->controller)($rule);
        static::assertEquals(new RedirectResponse('/app/rules/rule/2'), $response);
    }

    public function getController(): AbstractController
    {
        return new DuplicateRuleController($this->ruleRepository);
    }
}
