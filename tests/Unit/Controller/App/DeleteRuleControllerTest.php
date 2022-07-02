<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App;

use DR\GitCommitNotification\Controller\App\DeleteRuleController;
use DR\GitCommitNotification\Controller\App\RulesController;
use DR\GitCommitNotification\Entity\Config\Rule;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\DeleteRuleController
 * @covers ::__construct
 */
class DeleteRuleControllerTest extends AbstractControllerTestCase
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
        $controller = new DeleteRuleController($this->ruleRepository, $this->translator, null);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied');
        $controller((new Rule())->setUser(new User()));
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeWithUser(): void
    {
        $rule = (new Rule())->setUser($this->user);

        $this->ruleRepository->expects(self::once())->method('remove')->with($rule, true);
        $this->translator->expects(self::once())->method('trans')->willReturn('removed');
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
        return new DeleteRuleController($this->ruleRepository, $this->translator, $this->user);
    }
}
