<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\UrlQueryExtension;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @coversDefaultClass \DR\Review\Twig\UrlQueryExtension
 * @covers ::__construct
 */
class UrlQueryExtensionTest extends AbstractTestCase
{
    /**
     * @covers ::getFunctions
     */
    public function testGetFunctions(): void
    {
        $extension = new UrlQueryExtension($this->createRequestStack());
        $functions = $extension->getFunctions();

        static::assertCount(1, $functions);

        $function = $functions[0];
        static::assertSame('url_query_params', $function->getName());
    }

    /**
     * @covers ::getUrlQuery
     */
    public function testGetUrlQueryWithoutParams(): void
    {
        $request   = new Request(['foo' => 'bar']);
        $extension = new UrlQueryExtension($this->createRequestStack($request));
        static::assertSame('?foo=bar', $extension->getUrlQuery([]));
    }

    /**
     * @covers ::getUrlQuery
     */
    public function testGetUrlWithoutRequest(): void
    {
        $extension = new UrlQueryExtension(new RequestStack());
        static::assertSame('?foo=bar', $extension->getUrlQuery(['foo' => 'bar']));
    }

    /**
     * @covers ::getUrlQuery
     */
    public function testGetUrlQueryReplaceParams(): void
    {
        $request   = new Request(['foo' => 'bar']);
        $extension = new UrlQueryExtension($this->createRequestStack($request));
        static::assertSame('?foo=replaced', $extension->getUrlQuery(['foo' => 'replaced']));
    }

    /**
     * @covers ::getUrlQuery
     */
    public function testGetUrlQueryAppendParams(): void
    {
        $request   = new Request(['foo' => 'bar']);
        $extension = new UrlQueryExtension($this->createRequestStack($request));
        static::assertSame('?foo=bar&sherlock=holmes', $extension->getUrlQuery(['sherlock' => 'holmes']));
    }

    private function createRequestStack(?Request $request = null): RequestStack
    {
        $stack = new RequestStack();
        $stack->push($request ?? new Request());

        return $stack;
    }
}
