<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Controller\App\User\Gitlab\UserGitlabOAuth2FinishController;
use DR\Review\Service\Api\Gitlab\OAuth2ProviderFactory;
use DR\Review\Tests\AbstractTestCase;
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

        $actual = $this->factory->create();
        static::assertSame('https://example.com/oauth/authorize', $actual->getBaseAuthorizationUrl());
        static::assertSame('https://example.com/oauth/token', $actual->getBaseAccessTokenUrl([]));
    }
}
