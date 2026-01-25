<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Response;

use DR\Review\Response\ProblemJsonResponse;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Response;

#[CoversClass(ProblemJsonResponse::class)]
class ProblemJsonResponseTest extends AbstractTestCase
{
    public function testConstruct(): void
    {
        $response = new ProblemJsonResponse(['foobar' => 'foobar'], Response::HTTP_MULTIPLE_CHOICES, ['foo' => 'bar'], false);
        static::assertSame('application/problem+json', $response->headers->get('content-type'));
        static::assertSame('bar', $response->headers->get('foo'));
        static::assertSame(300, $response->getStatusCode());
        static::assertSame('{"foobar":"foobar"}', $response->getContent());
    }
}
