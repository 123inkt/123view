<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Response;

use DR\Review\Response\ProblemJsonResponse;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Response\ProblemJsonResponse
 * @covers ::__construct
 */
class ProblemJsonResponseTest extends AbstractTestCase
{
    /**
     * @covers ::__construct
     */
    public function testConstruct(): void
    {
        $response = new ProblemJsonResponse(['foobar'], 300, ['foo' => 'bar'], false);
        static::assertSame('application/problem+json', $response->headers->get('content-type'));
        static::assertSame('bar', $response->headers->get('foo'));
        static::assertSame(300, $response->getStatusCode());
        static::assertSame('["foobar"]', $response->getContent());
    }
}
