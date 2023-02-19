<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Response;

use DR\Review\Response\ProblemJsonResponseFactory;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @coversDefaultClass \DR\Review\Response\ProblemJsonResponseFactory
 * @covers ::__construct
 */
class ProblemJsonResponseFactoryTest extends AbstractTestCase
{
    /**
     * @covers ::createFromThrowable
     */
    public function testCreateFromThrowableHandlesHttpExceptionWithoutMessage(): void
    {
        $throwable = new HttpException(Response::HTTP_NOT_FOUND);
        $factory   = new ProblemJsonResponseFactory(false);
        $response  = $factory->createFromThrowable($throwable);
        static::assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        static::assertSame('{"type":"about:blank","status":404,"detail":[]}', $response->getContent());
    }

    /**
     * @covers ::createFromThrowable
     */
    public function testCreateFromThrowableHandlesHttpExceptionWithMessage(): void
    {
        $throwable = new HttpException(Response::HTTP_BAD_REQUEST, 'fooBar');
        $factory   = new ProblemJsonResponseFactory(false);
        $response  = $factory->createFromThrowable($throwable);
        static::assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        static::assertSame('{"type":"about:blank","title":"fooBar","status":400,"detail":[]}', $response->getContent());
    }

    /**
     * @covers ::createFromThrowable
     */
    public function testCreateFromThrowableHandlesAccessDeniedException(): void
    {
        $throwable = new AccessDeniedException('fooBar');
        $factory   = new ProblemJsonResponseFactory(false);
        $response  = $factory->createFromThrowable($throwable);
        static::assertSame(Response::HTTP_FORBIDDEN, $response->getStatusCode());
        static::assertSame('{"type":"about:blank","title":"fooBar","status":403,"detail":[]}', $response->getContent());
    }

    /**
     * @covers ::createFromThrowable
     * @throws JsonException
     */
    public function testCreateFromThrowableHandlesShowDetailsOnDebug(): void
    {
        $throwable = new Exception('fooBar');
        $factory   = new ProblemJsonResponseFactory(true);
        $response  = $factory->createFromThrowable($throwable);

        static::assertSame(500, $response->getStatusCode());
        $data = Json::decode((string)$response->getContent(), true);
        static::assertIsArray($data);
        static::assertSame(Response::$statusTexts[500], $data['title']);
        static::assertNotEmpty($data['detail']);
    }

    /**
     * @covers ::createFromThrowable
     */
    public function testCreateFromThrowableReturnsEmptyResponseWhenProductionOrMessageEmpty(): void
    {
        $throwable = new Exception();
        $factory   = new ProblemJsonResponseFactory(false);
        $response  = $factory->createFromThrowable($throwable);
        static::assertSame('{"type":"about:blank","title":"Internal Server Error","status":500,"detail":[]}', $response->getContent());
    }
}
