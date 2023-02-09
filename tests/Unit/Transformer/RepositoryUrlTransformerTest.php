<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Transformer;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Transformer\RepositoryUrlTransformer;
use League\Uri\Uri;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * @coversDefaultClass \DR\Review\Transformer\RepositoryUrlTransformer
 */
class RepositoryUrlTransformerTest extends AbstractTestCase
{
    private RepositoryUrlTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new RepositoryUrlTransformer();
    }

    /**
     * @covers ::transform
     */
    public function testTransformNull(): void
    {
        static::assertNull($this->transformer->transform(null));
    }

    /**
     * @covers ::transform
     */
    public function testTransformFailure(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Unable to transform value');
        $this->transformer->transform('foobar');
    }

    /**
     * @covers ::transform
     */
    public function testTransformSuccess(): void
    {
        $result = $this->transformer->transform(Uri::createFromString('https://sherlock:holmes@example.com/foobar?query#anchor'));
        static::assertSame(['url' => 'https://example.com/foobar?query#anchor', 'username' => 'sherlock', 'password' => ''], $result);
    }

    /**
     * @covers ::reverseTransform
     */
    public function testReverseTransformNull(): void
    {
        static::assertNull($this->transformer->reverseTransform(null));
    }

    /**
     * @covers ::reverseTransform
     */
    public function testReverseTransformSuccess(): void
    {
        $data = ['url' => 'https://example.com/foobar?query#anchor', 'username' => 'sherlock', 'password' => 'holmes'];
        $uri  = $this->transformer->reverseTransform($data);
        static::assertSame('https://sherlock:holmes@example.com/foobar?query#anchor', (string)$uri);
    }

    /**
     * @covers ::reverseTransform
     */
    public function testReverseTransformFailure(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Unable to transform value');
        $this->transformer->reverseTransform('foobar');
    }
}
