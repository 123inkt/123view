<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Utility;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Utility\FileUtil;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(FileUtil::class)]
class FileUtilTest extends AbstractTestCase
{
    #[TestWith(['test.png', 'image/png'])]
    #[TestWith(['test.jpg', 'image/jpg'])]
    #[TestWith(['test.jpeg', 'image/jpg'])]
    #[TestWith(['test.gif', 'image/gif'])]
    #[TestWith(['test.svg', 'image/svg+xml'])]
    #[TestWith(['test.pdf', 'application/pdf'])]
    #[TestWith(['test.md', 'text/markdown'])]
    #[TestWith(['test.txt', null])]
    public function testGetMimeType(string $filePath, ?string $expectedMimeType): void
    {
        self::assertSame($expectedMimeType, FileUtil::getMimeType($filePath));
    }

    #[TestWith(['image/png', true])]
    #[TestWith(['image/jpg', true])]
    #[TestWith(['image/gif', true])]
    #[TestWith(['image/svg+xml', true])]
    #[TestWith(['application/pdf', false])]
    #[TestWith(['text/markdown', false])]
    public function testIsImage(string $mimeType, bool $expected): void
    {
        self::assertSame($expected, FileUtil::isImage($mimeType));
    }

    #[TestWith(['image/png', true])]
    #[TestWith(['image/jpg', true])]
    #[TestWith(['image/gif', true])]
    #[TestWith(['application/pdf', true])]
    #[TestWith(['text/markdown', false])]
    #[TestWith(['text/plain', false])]
    public function testIsBinary(string $mimeType, bool $expected): void
    {
        self::assertSame($expected, FileUtil::isBinary($mimeType));
    }
}
