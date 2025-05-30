<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Notification\DeleteRuleController;
use DR\Review\Controller\App\Notification\RulesController;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Security\Voter\RuleVoter;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @extends AbstractControllerTestCase<DeleteRuleController>
 */
#[CoversClass(DeleteRuleController::class)]
class DeleteRuleControllerTest extends AbstractControllerTestCase
{
    private RuleRepository&MockObject      $ruleRepository;
    private TranslatorInterface&MockObject $translator;
    private User                           $user;

    protected function setUp(): void
    {
        $this->ruleRepository = $this->createMock(RuleRepository::class);
        $this->translator     = $this->createMock(TranslatorInterface::class);
        $this->user           = new User();
        parent::setUp();
    }

    public function testInvokeUserIsNotRuleOwner(): void
    {
        $userB = new User();
        $rule  = (new Rule())->setUser($userB);

        $this->expectDenyAccessUnlessGranted(RuleVoter::DELETE, $rule, false);
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access Denied.');
        ($this->controller)($rule);
    }

    public function testInvokeWithUser(): void
    {
        $rule = (new Rule())->setUser($this->user)->setName('name');

        $this->expectDenyAccessUnlessGranted(RuleVoter::DELETE, $rule);
        $this->ruleRepository->expects($this->once())->method('remove')->with($rule, true);
        $this->translator->expects($this->once())->method('trans')->willReturn('removed');
        $this->expectAddFlash('success', 'removed');
        $this->expectGenerateUrl(RulesController::class)->willReturn('redirect');

        $response = ($this->controller)($rule);
        $expected = new RedirectResponse('redirect');
        static::assertEquals($expected, $response);
    }

    /**
     * @inheritDoc
     */
    public function getController(): AbstractController
    {
        return new DeleteRuleController($this->ruleRepository, $this->translator);
    }
}
