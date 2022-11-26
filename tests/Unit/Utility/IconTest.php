<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Utility;

use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Utility\Icon;
use org\bovigo\vfs\vfsStream;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Utility\Icon
 */
class IconTest extends AbstractTestCase
{
    /**
     * @covers ::getBase64
     */
    public function testGetBase64(): void
    {
        $path = vfsStream::setup()->url() . '/example.png';
        file_put_contents($path, 'abcd');

        $result = Icon::getBase64($path);
        static::assertSame('data:image/png;base64,YWJjZA==', $result);
    }
}
