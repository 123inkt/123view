<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Utility;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Utility\Icon;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Icon::class)]
class IconTest extends AbstractTestCase
{
    public function testGetBase64(): void
    {
        $path = vfsStream::setup()->url() . '/example.png';
        file_put_contents($path, 'abcd');

        $result = Icon::getBase64($path);
        static::assertSame('data:image/png;base64,YWJjZA==', $result);
    }
}
