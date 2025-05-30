<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Notification;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Notification\RuleNotificationController;
use DR\Review\Doctrine\Type\MailThemeType;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Notification\Frequency;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Repository\Config\RuleNotificationRepository;
use DR\Review\Security\Voter\RuleVoter;
use DR\Review\Service\RuleProcessor;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\Mail\CommitsViewModel;
use DR\Review\ViewModelProvider\Mail\CommitsViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends AbstractControllerTestCase<RuleNotificationController>
 */
#[CoversClass(RuleNotificationController::class)]
class RuleNotificationControllerTest extends AbstractControllerTestCase
{
    private RuleProcessor&MockObject              $ruleProcessor;
    private RuleNotificationRepository&MockObject $notificationRepository;
    private CommitsViewModelProvider&MockObject   $viewModelProvider;

    protected function setUp(): void
    {
        $this->ruleProcessor          = $this->createMock(RuleProcessor::class);
        $this->notificationRepository = $this->createMock(RuleNotificationRepository::class);
        $this->viewModelProvider      = $this->createMock(CommitsViewModelProvider::class);
        parent::setUp();
    }

    public function testInvokeShouldDenyAccess(): void
    {
        $rule         = new Rule();
        $notification = new RuleNotification();
        $notification->setRule($rule);

        $this->expectDenyAccessUnlessGranted(RuleVoter::EDIT, $rule, false);

        $this->expectException(AccessDeniedException::class);
        ($this->controller)($notification);
    }

    public function testInvoke(): void
    {
        $options = new RuleOptions();
        $options->setFrequency(Frequency::ONCE_PER_DAY);
        $options->setTheme(MailThemeType::DARCULA);
        $rule = new Rule();
        $rule->setRuleOptions($options);
        $notification = new RuleNotification();
        $notification->setNotifyTimestamp(123456789);
        $notification->setRule($rule);
        $commit = $this->createMock(Commit::class);

        $viewModel = new CommitsViewModel([$commit], MailThemeType::DARCULA);

        $this->expectDenyAccessUnlessGranted(RuleVoter::EDIT, $rule);
        $this->ruleProcessor->expects($this->once())->method('processRule')->willReturn([$commit]);
        $this->viewModelProvider->expects($this->once())
            ->method('getCommitsViewModel')
            ->with([$commit], $rule, $notification)
            ->willReturn($viewModel);
        $this->expectRender('mail/mail.commits.html.twig', ['viewModel' => $viewModel]);
        $this->notificationRepository->expects($this->once())->method('save')->with($notification);

        $response = ($this->controller)($notification);

        static::assertTrue($response->headers->has('Content-Security-Policy'));
        static::assertTrue($notification->isRead());
    }

    public function testInvokeNoCommits(): void
    {
        $options = new RuleOptions();
        $options->setFrequency(Frequency::ONCE_PER_DAY);
        $options->setTheme(MailThemeType::DARCULA);
        $rule = new Rule();
        $rule->setRuleOptions($options);
        $notification = new RuleNotification();
        $notification->setNotifyTimestamp(123456789);
        $notification->setRule($rule);

        $this->expectDenyAccessUnlessGranted(RuleVoter::EDIT, $rule);
        $this->ruleProcessor->expects($this->once())->method('processRule')->willReturn([]);
        $this->notificationRepository->expects($this->once())->method('save')->with($notification);

        static::assertEquals(
            new Response('No (more) revisions found for this notification rule', headers: ['Content-Type' => 'text/plain']),
            ($this->controller)($notification)
        );
    }

    public function getController(): AbstractController
    {
        return new RuleNotificationController($this->ruleProcessor, $this->notificationRepository, $this->viewModelProvider);
    }
}
