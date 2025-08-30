<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Url;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Project\ProjectsController;
use DR\Review\Controller\App\Url\ShortUrlController;
use DR\Review\Entity\Url\ShortUrl;
use DR\Review\Repository\Url\ShortUrlRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use League\Uri\Http;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<ShortUrlController>
 */
#[CoversClass(ShortUrlController::class)]
class ShortUrlControllerTest extends AbstractControllerTestCase
{
    private ShortUrlRepository&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(ShortUrlRepository::class);
        parent::setUp();
    }

    public function testInvokeWithValidShortKey(): void
    {
        $shortKey    = 'abc123';
        $originalUrl = '/app/review/123/file/src/test.php';

        $uri      = Http::new($originalUrl);
        $shortUrl = (new ShortUrl())->setShortKey($shortKey)->setOriginalUrl($uri);

        $this->repository->expects($this->once())->method('findOneBy')->with(['shortKey' => $shortKey])->willReturn($shortUrl);

        $response = ($this->controller)($shortKey);
        static::assertSame($originalUrl, $response->getTargetUrl());
    }

    public function testInvokeWithInvalidShortKey(): void
    {
        $shortKey = 'nonexistent';

        $this->repository->expects($this->once())->method('findOneBy')->with(['shortKey' => $shortKey])->willReturn(null);

        $this->expectRedirectToRoute(ProjectsController::class);
        ($this->controller)($shortKey);
    }

    public function getController(): AbstractController
    {
        return new ShortUrlController($this->repository);
    }
}
