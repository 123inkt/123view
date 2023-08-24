<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Transformer;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Transformer\RepositoryUrlTransformer;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Form\Exception\TransformationFailedException;

#[CoversClass(RepositoryUrlTransformer::class)]
class RepositoryUrlTransformerTest extends AbstractTestCase
{
    private RepositoryUrlTransformer $transformer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transformer = new RepositoryUrlTransformer();
    }

    public function testTransformNull(): void
    {
        static::assertNull($this->transformer->transform(null));
    }

    public function testTransformFailure(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Unable to transform value');
        $this->transformer->transform('foobar'); // @phpstan-ignore-line
    }

    public function testTransformSuccess(): void
    {
        $result = $this->transformer->transform(Uri::new('https://sherlock:holmes@example.com/foobar?query#anchor'));
        static::assertSame('https://example.com/foobar?query#anchor', $result);
    }

    public function testReverseTransformNull(): void
    {
        static::assertNull($this->transformer->reverseTransform(null));
    }

    public function testReverseTransformSuccess(): void
    {
        $data = 'https://sherlock:holmes@example.com/foobar?query#anchor';
        $uri  = $this->transformer->reverseTransform($data);
        static::assertSame('https://example.com/foobar?query#anchor', (string)$uri);
    }

    public function testReverseTransformFailure(): void
    {
        $this->expectException(TransformationFailedException::class);
        $this->expectExceptionMessage('Unable to transform value');
        $this->transformer->reverseTransform(['foobar']); // @phpstan-ignore-line
    }
}
