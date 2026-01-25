<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\ReviewExtension;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewExtension::class)]
class ReviewExtensionTest extends AbstractTestCase
{
    private ReviewExtension $extension;

    public function setUp(): void
    {
        parent::setUp();
        $this->extension = new ReviewExtension();
    }

    public function testFilePathWithHash(): void
    {
        $diffFile                = new DiffFile();
        $diffFile->filePathAfter = 'filepath';
        $diffFile->hashEnd       = 'hash';

        static::assertSame("filepath:hash", $this->extension->filePath($diffFile));
    }

    public function testFilePathWithoutHash(): void
    {
        $diffFile                = new DiffFile();
        $diffFile->filePathAfter = 'filepath';

        static::assertSame("filepath", $this->extension->filePath($diffFile));
    }
}
