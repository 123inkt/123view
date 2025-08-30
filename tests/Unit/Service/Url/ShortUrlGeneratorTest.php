<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Url;

use DR\Review\Entity\Url\ShortUrl;
use DR\Review\Service\Url\ShortUrlCreationService;
use DR\Review\Service\Url\ShortUrlGenerator;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;

#[CoversClass(ShortUrlGenerator::class)]
class ShortUrlGeneratorTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private ShortUrlCreationService&MockObject $shortUrlCreationService;
    private ShortUrlGenerator $shortUrlGenerator;
    private RequestContext&MockObject $context;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->shortUrlCreationService = $this->createMock(ShortUrlCreationService::class);
        $this->context = $this->createMock(RequestContext::class);
        $this->shortUrlGenerator = new ShortUrlGenerator($this->urlGenerator, $this->shortUrlCreationService);
    }

    public function testGenerateWithAbsolutePath(): void
    {
        $routeName = 'app_review';
        $parameters = ['id' => 123];
        $originalUrl = '/app/review/123';
        $shortKey = 'abc123';

        $shortUrl = new ShortUrl();
        $shortUrl->setShortKey($shortKey);
        $shortUrl->setOriginalUrl(Http::new($originalUrl));
        $shortUrl->setCreateTimestamp(time());

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn($originalUrl);

        $this->shortUrlCreationService->expects($this->once())
            ->method('createShortUrl')
            ->with(Http::new($originalUrl))
            ->willReturn($shortUrl);

        $result = $this->shortUrlGenerator->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH);

        static::assertSame('/url/' . $shortKey, $result);
    }

    public function testGenerateWithAbsoluteUrl(): void
    {
        $routeName = 'app_review';
        $parameters = ['id' => 456];
        $originalUrl = 'https://example.com/app/review/456';
        $shortKey = 'xyz789';

        $shortUrl = new ShortUrl();
        $shortUrl->setShortKey($shortKey);
        $shortUrl->setOriginalUrl(Http::new($originalUrl));

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn($originalUrl);

        $this->urlGenerator->expects($this->once())
            ->method('getContext')
            ->willReturn($this->context);

        $this->context->expects($this->once())
            ->method('getScheme')
            ->willReturn('https');

        $this->context->expects($this->once())
            ->method('getHost')
            ->willReturn('example.com');

        $this->context->expects($this->once())
            ->method('getHttpsPort')
            ->willReturn(443);

        $this->shortUrlCreationService->expects($this->once())
            ->method('createShortUrl')
            ->willReturn($shortUrl);

        $result = $this->shortUrlGenerator->generate($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);

        static::assertSame('https://example.com/url/' . $shortKey, $result);
    }

    public function testGenerateWithNetworkPath(): void
    {
        $routeName = 'app_project';
        $parameters = ['slug' => 'test'];
        $originalUrl = '/app/project/test';
        $shortKey = 'net123';

        $shortUrl = new ShortUrl();
        $shortUrl->setShortKey($shortKey);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($routeName, $parameters, UrlGeneratorInterface::NETWORK_PATH)
            ->willReturn($originalUrl);

        $this->urlGenerator->expects($this->once())
            ->method('getContext')
            ->willReturn($this->context);

        $this->context->expects($this->once())
            ->method('getHost')
            ->willReturn('localhost');

        $this->context->expects($this->once())
            ->method('getScheme')
            ->willReturn('http');

        $this->context->expects($this->once())
            ->method('getHttpPort')
            ->willReturn(8080);

        $this->shortUrlCreationService->expects($this->once())
            ->method('createShortUrl')
            ->willReturn($shortUrl);

        $result = $this->shortUrlGenerator->generate($routeName, $parameters, UrlGeneratorInterface::NETWORK_PATH);

        static::assertSame('//localhost:8080/url/' . $shortKey, $result);
    }

    public function testGenerateWithCustomPortHttps(): void
    {
        $routeName = 'app_test';
        $originalUrl = '/app/test';
        $shortKey = 'port443';

        $shortUrl = new ShortUrl();
        $shortUrl->setShortKey($shortKey);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->willReturn($originalUrl);

        $this->urlGenerator->expects($this->once())
            ->method('getContext')
            ->willReturn($this->context);

        $this->context->expects($this->once())
            ->method('getScheme')
            ->willReturn('https');

        $this->context->expects($this->once())
            ->method('getHost')
            ->willReturn('secure.example.com');

        $this->context->expects($this->once())
            ->method('getHttpsPort')
            ->willReturn(8443);

        $this->shortUrlCreationService->expects($this->once())
            ->method('createShortUrl')
            ->willReturn($shortUrl);

        $result = $this->shortUrlGenerator->generate($routeName, [], UrlGeneratorInterface::ABSOLUTE_URL);

        static::assertSame('https://secure.example.com:8443/url/' . $shortKey, $result);
    }

    public function testGenerateWithRelativePath(): void
    {
        $routeName = 'app_relative';
        $originalUrl = '/relative/path';
        $shortKey = 'rel123';

        $shortUrl = new ShortUrl();
        $shortUrl->setShortKey($shortKey);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($routeName, [], UrlGeneratorInterface::RELATIVE_PATH)
            ->willReturn($originalUrl);

        $this->shortUrlCreationService->expects($this->once())
            ->method('createShortUrl')
            ->willReturn($shortUrl);

        $result = $this->shortUrlGenerator->generate($routeName, [], UrlGeneratorInterface::RELATIVE_PATH);

        static::assertSame('/url/' . $shortKey, $result);
    }

    public function testGetContext(): void
    {
        $this->urlGenerator->expects($this->once())
            ->method('getContext')
            ->willReturn($this->context);

        $result = $this->shortUrlGenerator->getContext();

        static::assertSame($this->context, $result);
    }

    public function testSetContext(): void
    {
        $newContext = $this->createMock(RequestContext::class);

        $this->urlGenerator->expects($this->once())
            ->method('setContext')
            ->with($newContext);

        $this->shortUrlGenerator->setContext($newContext);
    }

    public function testGenerateCreatesUriFromOriginalUrl(): void
    {
        $routeName = 'app_complex';
        $parameters = ['id' => 789, 'slug' => 'test'];
        $originalUrl = '/app/complex/789/test?param=value';
        $shortKey = 'complex1';

        $shortUrl = new ShortUrl();
        $shortUrl->setShortKey($shortKey);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($routeName, $parameters, UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn($originalUrl);

        $this->shortUrlCreationService->expects($this->once())
            ->method('createShortUrl')
            ->with($this->callback(function ($uri) use ($originalUrl) {
                return $uri instanceof \Psr\Http\Message\UriInterface && 
                       (string) $uri === $originalUrl;
            }))
            ->willReturn($shortUrl);

        $result = $this->shortUrlGenerator->generate($routeName, $parameters);

        static::assertSame('/url/' . $shortKey, $result);
    }

    public function testGenerateWithEmptyParameters(): void
    {
        $routeName = 'app_home';
        $originalUrl = '/';
        $shortKey = 'home123';

        $shortUrl = new ShortUrl();
        $shortUrl->setShortKey($shortKey);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($routeName, [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn($originalUrl);

        $this->shortUrlCreationService->expects($this->once())
            ->method('createShortUrl')
            ->willReturn($shortUrl);

        $result = $this->shortUrlGenerator->generate($routeName);

        static::assertSame('/url/' . $shortKey, $result);
    }

    public function testGenerateWithDefaultReferenceType(): void
    {
        $routeName = 'app_default';
        $originalUrl = '/app/default';
        $shortKey = 'def123';

        $shortUrl = new ShortUrl();
        $shortUrl->setShortKey($shortKey);

        // Should use ABSOLUTE_PATH as default
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with($routeName, [], UrlGeneratorInterface::ABSOLUTE_PATH)
            ->willReturn($originalUrl);

        $this->shortUrlCreationService->expects($this->once())
            ->method('createShortUrl')
            ->willReturn($shortUrl);

        // Call without reference type parameter
        $result = $this->shortUrlGenerator->generate($routeName);

        static::assertSame('/url/' . $shortKey, $result);
    }
}