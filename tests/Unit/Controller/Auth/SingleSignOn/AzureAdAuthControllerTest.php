<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\Auth\SingleSignOn;

use DR\GitCommitNotification\Controller\Auth\SingleSignOn\AzureAdAuthController;
use DR\GitCommitNotification\Controller\Auth\SingleSignOn\AzureAdCallbackController;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TheNetworg\OAuth2\Client\Provider\Azure;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\Auth\SingleSignOn\AzureAdAuthController
 * @covers ::__construct
 */
class AzureAdAuthControllerTest extends AbstractControllerTestCase
{
    /** @var Azure&MockObject */
    private Azure $azureProvider;

    protected function setUp(): void
    {
        $this->azureProvider = $this->createMock(Azure::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $request                    = new Request(['foo' => 'bar']);
        $url                        = 'http://callback-url';
        $this->azureProvider->scope = ['userid'];

        $this->expectGenerateUrl(AzureAdCallbackController::class, [], UrlGeneratorInterface::ABSOLUTE_URL)->willReturn($url);
        $this->azureProvider
            ->expects(self::once())
            ->method('getAuthorizationUrl')
            ->with(
                self::callback(static function (array $options) use ($url) {
                    static::assertSame(['scope' => ['userid'], 'redirectUri' => $url, 'state' => '{"foo":"bar"}'], $options);

                    return true;
                })
            )
            ->willReturn('authorizationUrl');

        $response = ($this->controller)($request);
        static::assertEquals(new RedirectResponse('authorizationUrl'), $response);
    }

    public function getController(): AbstractController
    {
        return new AzureAdAuthController($this->azureProvider);
    }
}
