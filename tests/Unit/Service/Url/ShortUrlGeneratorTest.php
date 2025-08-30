<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Url;

use DR\Review\Entity\Url\ShortUrl;
use DR\Review\Service\Url\ShortUrlCreationService;
use DR\Review\Service\Url\ShortUrlGenerator;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(ShortUrlGenerator::class)]
class ShortUrlGeneratorTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject   $urlGenerator;
    private ShortUrlCreationService&MockObject $shortUrlCreationService;
    private ShortUrlGenerator                  $shortUrlGenerator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator            = $this->createMock(UrlGeneratorInterface::class);
        $this->shortUrlCreationService = $this->createMock(ShortUrlCreationService::class);
        $this->shortUrlGenerator       = new ShortUrlGenerator($this->urlGenerator, $this->shortUrlCreationService);
    }

    public function testGenerateWithAbsolutePath(): void
    {
        $routeName   = 'app_review';
        $parameters  = ['id' => 123];
        $originalUrl = '/app/review/123';
        $shortKey    = 'abc123';
        $shortUri    = '/url/foobar';

        $shortUrl = new ShortUrl();
        $shortUrl->setShortKey($shortKey);
        $shortUrl->setOriginalUrl(Uri::new($originalUrl));
        $shortUrl->setCreateTimestamp(time());

        $this->urlGenerator->expects($this->exactly(2))
            ->method('generate')
            ->willReturn($originalUrl, $shortUri);

        $this->shortUrlCreationService->expects($this->once())
            ->method('createShortUrl')
            ->with(Uri::new($originalUrl))
            ->willReturn($shortUrl);

        $result = $this->shortUrlGenerator->generate($routeName, $parameters);

        static::assertSame($shortUri, $result);
    }
}
