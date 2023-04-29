<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\IO;

use DR\Review\Service\IO\FilePathNormalizer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FilePathNormalizer::class)]
class FilePathNormalizerTest extends AbstractTestCase
{
    private FilePathNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new FilePathNormalizer();
    }

    public function testNormalize(): void
    {
        static::assertSame('test.txt', $this->normalizer->normalize('/foo/bar/', '/foo/bar/test.txt'));
        static::assertSame('test.txt', $this->normalizer->normalize('/foo/bar', '/foo/bar/test.txt'));
        static::assertSame('foo/bar/test.txt', $this->normalizer->normalize('/invalid/', '/foo/bar/test.txt'));
        static::assertSame('test.txt', $this->normalizer->normalize('\\foo\\bar', '/foo/bar/test.txt'));
        static::assertSame('bar/test.txt', $this->normalizer->normalize('/foo', '\\foo\\bar\\test.txt'));
    }
}
