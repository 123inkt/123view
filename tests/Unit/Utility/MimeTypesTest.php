<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Utility;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Utility\MimeTypes;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(MimeTypes::class)]
class MimeTypesTest extends AbstractTestCase
{
    #[TestWith(['test.png', 'image/png'])]
    #[TestWith(['test.jpg', 'image/jpeg'])]
    #[TestWith(['test.jpeg', 'image/jpeg'])]
    #[TestWith(['test.gif', 'image/gif'])]
    #[TestWith(['test.svg', 'image/svg+xml'])]
    #[TestWith(['test.pdf', 'application/pdf'])]
    #[TestWith(['test.md', 'text/markdown'])]
    #[TestWith(['test.foo', null])]
    public function testGetMimeType(string $filePath, ?string $expectedMimeType): void
    {
        static::assertSame($expectedMimeType, MimeTypes::getMimeType($filePath));
    }

    #[TestWith(['image/png', true])]
    #[TestWith(['image/jpg', true])]
    #[TestWith(['image/gif', true])]
    #[TestWith(['image/svg+xml', true])]
    #[TestWith(['application/pdf', false])]
    #[TestWith(['text/markdown', false])]
    public function testIsImage(string $mimeType, bool $expected): void
    {
        static::assertSame($expected, MimeTypes::isImage($mimeType));
    }
}
