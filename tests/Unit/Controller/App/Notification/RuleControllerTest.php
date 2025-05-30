<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Notification\RuleController;
use DR\Review\Controller\App\Notification\RulesController;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\User\User;
use DR\Review\Form\Rule\EditRuleFormType;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Security\Voter\RuleVoter;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Rule\EditRuleViewModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends AbstractControllerTestCase<RuleController>
 */
#[CoversClass(RuleController::class)]
class RuleControllerTest extends AbstractControllerTestCase
{
    private RuleRepository&MockObject $ruleRepository;
    private User                      $user;

    protected function setUp(): void
    {
        $this->ruleRepository = $this->createMock(RuleRepository::class);
        $this->user           = new User();
        parent::setUp();
    }

    public function testInvokeUserIsNotRuleOwner(): void
    {
        $userB = new User();
        $rule  = (new Rule())->setUser($userB);

        $this->expectDenyAccessUnlessGranted(RuleVoter::EDIT, $rule, false);
        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access Denied');
        ($this->controller)(new Request(), $rule);
    }

    public function testInvokeUnknownRuleId(): void
    {
        $this->expectException(NotFoundHttpException::class);
        $this->expectExceptionMessage('Rule not found');
        ($this->controller)(new Request([], [], ['id' => -1]), null);
    }

    public function testInvokeWithUser(): void
    {
        $request = new Request();
        $rule    = (new Rule())->setUser($this->user);

        $form = $this->expectCreateForm(EditRuleFormType::class, ['rule' => $rule]);
        $form->handleRequest($request);
        $form->isSubmittedWillReturn(true);
        $form->isValidWillReturn(true);

        $this->expectDenyAccessUnlessGranted(RuleVoter::EDIT, $rule);
        $this->ruleRepository->expects($this->once())->method('save')->with($rule, true);
        $this->expectAddFlash('success', 'rule.successful.saved');
        $this->expectGenerateUrl(RulesController::class)->willReturn('redirect');

        $response = ($this->controller)($request, $rule);
        $expected = new RedirectResponse('redirect');
        static::assertEquals($expected, $response);
    }

    public function testInvokeWithUserNotSubmitted(): void
    {
        $request = new Request();
        $rule    = (new Rule())->setUser($this->user);

        $formView = $this->createMock(FormView::class);

        $form = $this->expectCreateForm(EditRuleFormType::class, ['rule' => $rule]);
        $form->handleRequest($request);
        $form->isSubmittedWillReturn(false);
        $form->createViewWillReturn($formView);

        $this->expectDenyAccessUnlessGranted(RuleVoter::EDIT, $rule);
        $this->ruleRepository->expects(self::never())->method('save');

        $response = ($this->controller)($request, $rule);
        static::assertIsArray($response);
        static::assertArrayHasKey('editRuleModel', $response);
        static::assertEquals((new EditRuleViewModel())->setForm($formView), $response['editRuleModel']);
    }

    public function getController(): AbstractController
    {
        return new RuleController($this->ruleRepository);
    }
}
