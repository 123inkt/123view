<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModelProvider\Mail;

use DR\Review\Controller\App\Notification\RuleNotificationReadController;
use DR\Review\Doctrine\Type\MailThemeType;
use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Notification\RuleNotification;
use DR\Review\Entity\Notification\RuleOptions;
use DR\Review\Service\Notification\RuleNotificationTokenGenerator;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\Mail\CommitsViewModel;
use DR\Review\ViewModelProvider\Mail\CommitsViewModelProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(CommitsViewModelProvider::class)]
class CommitsViewModelProviderTest extends AbstractTestCase
{
    private RuleNotificationTokenGenerator&MockObject $tokenGenerator;
    private UrlGeneratorInterface&MockObject          $urlGenerator;
    private CommitsViewModelProvider                  $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenGenerator = $this->createMock(RuleNotificationTokenGenerator::class);
        $this->urlGenerator   = $this->createMock(UrlGeneratorInterface::class);
        $this->provider       = new CommitsViewModelProvider($this->tokenGenerator, $this->urlGenerator);
    }

    public function testGetCommitsViewModel(): void
    {
        $notification = new RuleNotification();
        $notification->setId(123);
        $rule = new Rule();
        $rule->setRuleOptions(new RuleOptions());
        $commit = static::createStub(Commit::class);

        $this->tokenGenerator->expects($this->once())->method('generate')->with($notification)->willReturn('token');
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(RuleNotificationReadController::class, ['id' => 123, 'token' => 'token'])
            ->willReturn('url');

        $expected  = new CommitsViewModel([$commit], MailThemeType::UPSOURCE, 'url');
        $viewModel = $this->provider->getCommitsViewModel([$commit], $rule, $notification);
        static::assertEquals($expected, $viewModel);
    }
}
