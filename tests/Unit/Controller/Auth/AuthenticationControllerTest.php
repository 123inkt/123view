<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\Auth;

use DR\GitCommitNotification\Controller\Auth\AuthenticationController;
use DR\GitCommitNotification\Controller\Auth\SingleSignOn\AzureAdAuthController;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\Auth\AuthenticationController
 * @covers ::__construct
 */
class AuthenticationControllerTest extends AbstractControllerTestCase
{
    /** @var TranslatorInterface&MockObject */
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        $this->translator = $this->createMock(TranslatorInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $request = new Request(['error_message' => 'pretty bad']);

        $this->translator->expects(self::once())->method('trans')->with('page.title.single.sign.on')->willReturn('page title');
        $this->expectGenerateUrl(AzureAdAuthController::class)->willReturn('http://azure.ad.auth.controller');

        $result = ($this->controller)($request);
        static::assertSame(
            ['page_title' => 'page title', 'error_message' => 'pretty bad', 'azure_ad_url' => 'http://azure.ad.auth.controller'],
            $result
        );
    }

    public function getController(): AbstractController
    {
        return new AuthenticationController($this->translator);
    }
}
