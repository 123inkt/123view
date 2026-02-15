<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber;

use DR\Review\Controller\App\User\Gitlab\UserGitlabOAuth2FinishController;
use DR\Review\Controller\App\User\Gitlab\UserGitlabOAuth2StartController;
use DR\Review\Controller\App\User\UserMandatoryGitlabSyncController;
use DR\Review\Controller\Auth\LogoutController;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\User\GitAccessToken;
use DR\Review\Entity\User\User;
use DR\Review\EventSubscriber\MandatoryGitlabSyncSubscriber;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(MandatoryGitlabSyncSubscriber::class)]
class MandatoryGitlabSyncSubscriberTest extends AbstractTestCase
{
    private Security&MockObject              $security;
    private UrlGeneratorInterface&MockObject $urlGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->security     = $this->createMock(Security::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
    }

    public function testInvokeSkipsWhenUserNotLoggedIn(): void
    {
        $subscriber = $this->createSubscriber();
        $event      = $this->createRequestEvent();

        $this->security->expects($this->once())->method('getUser')->willReturn(null);
        $this->urlGenerator->expects($this->never())->method('generate');

        ($subscriber)($event);

        static::assertNull($event->getResponse());
    }

    #[TestWith([UserGitlabOAuth2StartController::class])]
    #[TestWith([UserGitlabOAuth2FinishController::class])]
    #[TestWith([UserMandatoryGitlabSyncController::class])]
    #[TestWith([LogoutController::class])]
    public function testInvokeSkipsForAllowedControllers(string $controllerClass): void
    {
        $subscriber = $this->createSubscriber();
        $event      = $this->createRequestEvent($controllerClass);
        $user       = new User();

        $this->security->expects($this->once())->method('getUser')->willReturn($user);
        $this->urlGenerator->expects($this->never())->method('generate');

        ($subscriber)($event);

        static::assertNull($event->getResponse());
    }

    public function testInvokeSkipsWhenSyncNotConfigured(): void
    {
        $subscriber = $this->createSubscriber(false, false, false);
        $event      = $this->createRequestEvent();
        $user       = new User();

        $this->security->expects($this->once())->method('getUser')->willReturn($user);
        $this->urlGenerator->expects($this->never())->method('generate');

        ($subscriber)($event);

        static::assertNull($event->getResponse());
    }

    public function testInvokeSkipsSyncIsNotMandatory(): void
    {
        $subscriber = $this->createSubscriber(true, true, false);
        $event      = $this->createRequestEvent();
        $user       = new User();

        $this->security->expects($this->once())->method('getUser')->willReturn($user);
        $this->urlGenerator->expects($this->never())->method('generate');

        ($subscriber)($event);

        static::assertNull($event->getResponse());
    }

    public function testInvokeSkipsWhenUserHasGitlabToken(): void
    {
        $subscriber  = $this->createSubscriber();
        $event       = $this->createRequestEvent();
        $gitlabToken = (new GitAccessToken())->setGitType(RepositoryGitType::GITLAB);

        $user = new User();
        $user->getGitAccessTokens()->add($gitlabToken);

        $this->security->expects($this->once())->method('getUser')->willReturn($user);
        $this->urlGenerator->expects($this->never())->method('generate');

        ($subscriber)($event);

        static::assertNull($event->getResponse());
    }

    public function testInvokeSkipsWhenInvokingApiUrl(): void
    {
        $subscriber = $this->createSubscriber();
        $event      = $this->createRequestEvent(requestUri: '/api/some-endpoint');
        $user       = new User();

        $this->security->expects($this->once())->method('getUser')->willReturn($user);
        $this->urlGenerator->expects($this->never())->method('generate');

        ($subscriber)($event);

        static::assertNull($event->getResponse());
    }

    public function testInvokeRedirectsToMandatorySyncPage(): void
    {
        $subscriber  = $this->createSubscriber();
        $event       = $this->createRequestEvent();
        $user        = new User();
        $expectedUrl = '/mandatory-gitlab-sync';

        $this->security->expects($this->once())->method('getUser')->willReturn($user);
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(UserMandatoryGitlabSyncController::class)
            ->willReturn($expectedUrl);

        ($subscriber)($event);

        $response = $event->getResponse();
        static::assertInstanceOf(RedirectResponse::class, $response);
        static::assertSame($expectedUrl, $response->getTargetUrl());
    }

    private function createSubscriber(
        bool $gitlabCommentSyncEnabled = true,
        bool $gitlabReviewerSyncEnabled = true,
        bool $gitlabSyncMandatory = true
    ): MandatoryGitlabSyncSubscriber {
        return new MandatoryGitlabSyncSubscriber(
            $gitlabCommentSyncEnabled,
            $gitlabReviewerSyncEnabled,
            $gitlabSyncMandatory,
            $this->security,
            $this->urlGenerator
        );
    }

    private function createRequestEvent(string $controller = 'some.controller', string $requestUri = '/app/test'): RequestEvent
    {
        $request             = new Request(server: ['REQUEST_URI' => $requestUri]);
        $request->attributes = new ParameterBag(['_controller' => $controller]);

        return new RequestEvent(static::createStub(HttpKernelInterface::class), $request, HttpKernelInterface::MAIN_REQUEST);
    }
}
