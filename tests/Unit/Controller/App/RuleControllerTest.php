<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App;

use DR\GitCommitNotification\Controller\App\RuleController;
use DR\GitCommitNotification\Controller\App\RulesController;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Form\EditRuleFormType;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use DR\GitCommitNotification\ViewModel\App\EditRuleViewModel;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\RuleController
 * @covers ::__construct
 */
class RuleControllerTest extends AbstractControllerTestCase
{
    /** @var MockObject&RuleRepository */
    private RuleRepository $ruleRepository;
    /** @var TranslatorInterface&MockObject */
    private TranslatorInterface $translator;
    private User                $user;

    protected function setUp(): void
    {
        $this->ruleRepository = $this->createMock(RuleRepository::class);
        $this->translator     = $this->createMock(TranslatorInterface::class);
        $this->user           = new User();
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeWithoutUser(): void
    {
        $controller = new RuleController($this->ruleRepository, $this->translator, null);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied');
        $controller(new Request(), (new Rule())->setUser(new User()));
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeWithUser(): void
    {
        $request = new Request();
        $rule    = (new Rule())->setUser($this->user);

        $form = $this->expectCreateForm(EditRuleFormType::class, ['rule' => $rule]);
        $form->handleRequest($request);
        $form->isSubmittedWillReturn(true);
        $form->isValidWillReturn(true);

        $this->ruleRepository->expects(self::once())->method('add')->with($rule, true);
        $this->translator->expects(self::once())->method('trans')->willReturn('added');
        $this->expectAddFlash('success', 'added');
        $this->expectGenerateUrl(RulesController::class)->willReturn('redirect');

        $response = ($this->controller)($request, $rule);
        $expected = new RedirectResponse('redirect');
        static::assertEquals($expected, $response);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeWithUserNotSubmitted(): void
    {
        $request = new Request();
        $rule    = (new Rule())->setUser($this->user);

        $formView = $this->createMock(FormView::class);

        $form = $this->expectCreateForm(EditRuleFormType::class, ['rule' => $rule]);
        $form->handleRequest($request);
        $form->isSubmittedWillReturn(false);
        $form->createViewWillReturn($formView);

        $this->ruleRepository->expects(self::never())->method('add');

        $response = ($this->controller)($request, $rule);
        static::assertIsArray($response);
        static::assertArrayHasKey('editRuleModel', $response);
        static::assertEquals((new EditRuleViewModel())->setForm($formView), $response['editRuleModel']);
    }

    public function getController(): AbstractController
    {
        return new RuleController($this->ruleRepository, $this->translator, $this->user);
    }
}
