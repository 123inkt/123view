<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Controller\App\User\Gitlab\UserGitlabOAuth2FinishController;
use DR\Review\Service\Api\Gitlab\OAuth2ProviderFactory;
use DR\Review\Tests\AbstractTestCase;
use League\OAuth2\Client\Provider\GenericProvider as OAuth2Provider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(OAuth2ProviderFactory::class)]
class OAuth2ProviderFactoryTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private OAuth2ProviderFactory            $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->factory      = new OAuth2ProviderFactory('https://example.com/', 'client-id', 'client-secret', $this->urlGenerator);
    }

    public function testCreate(): void
    {
        $this->urlGenerator->expects($this->once())->method('generate')
            ->with(UserGitlabOAuth2FinishController::class, [], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn('https://example.com/redirect');

        $expected = new OAuth2Provider(
            [
                'clientId'                => 'client-id',
                'clientSecret'            => 'client-secret',
                'redirectUri'             => 'https://example.com/redirect',
                'urlAuthorize'            => 'https://example.com/oauth/authorize',
                'urlAccessToken'          => 'https://example.com/oauth/token',
                'urlResourceOwnerDetails' => null,
                'pkceMethod'              => OAuth2Provider::PKCE_METHOD_S256
            ]
        );

        static::assertEquals($expected, $this->factory->create());
    }
}
