<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\Auth\SingleSignOn;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\Auth\SingleSignOn\AzureAdAuthController;
use DR\Review\Controller\Auth\SingleSignOn\AzureAdCallbackController;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use TheNetworg\OAuth2\Client\Provider\Azure;

/**
 * @extends AbstractControllerTestCase<AzureAdAuthController>
 */
#[CoversClass(AzureAdAuthController::class)]
class AzureAdAuthControllerTest extends AbstractControllerTestCase
{
    private Azure&MockObject $azureProvider;

    protected function setUp(): void
    {
        $this->azureProvider = $this->createMock(Azure::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request                    = new Request(['foo' => 'bar']);
        $url                        = 'https://callback-url';
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
