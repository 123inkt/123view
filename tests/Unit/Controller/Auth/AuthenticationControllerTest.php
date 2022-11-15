<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\Auth;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ProjectsController;
use DR\GitCommitNotification\Controller\Auth\AuthenticationController;
use DR\GitCommitNotification\Controller\Auth\SingleSignOn\AzureAdAuthController;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Security;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\Auth\AuthenticationController
 * @covers ::__construct
 */
class AuthenticationControllerTest extends AbstractControllerTestCase
{
    private TranslatorInterface&MockObject $translator;
    private Security&MockObject            $security;

    protected function setUp(): void
    {
        $this->security   = $this->createMock(Security::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $request = new Request(['error_message' => 'pretty bad', 'next' => 'next-url']);

        $this->security->expects(self::once())->method('getUser')->willReturn(null);
        $this->translator->expects(self::once())->method('trans')->with('page.title.single.sign.on')->willReturn('page title');
        $this->expectGenerateUrl(AzureAdAuthController::class, ['next' => 'next-url'])->willReturn('http://azure.ad.auth.controller');

        $result = ($this->controller)($request);
        static::assertSame(
            ['page_title' => 'page title', 'azure_ad_url' => 'http://azure.ad.auth.controller'],
            $result
        );
    }

    /**
     * @covers ::__invoke
     */
    public function testInvokeShouldRedirectUser(): void
    {
        $user    = new User();
        $request = new Request();

        $this->expectRedirectToRoute(ProjectsController::class)->willReturn('redirect-url');
        $this->security->expects(self::once())->method('getUser')->willReturn($user);
        $this->translator->expects(self::never())->method('trans');

        $result = ($this->controller)($request);
        static::assertInstanceOf(RedirectResponse::class, $result);
    }

    public function getController(): AbstractController
    {
        return new AuthenticationController($this->translator, $this->security);
    }
}
