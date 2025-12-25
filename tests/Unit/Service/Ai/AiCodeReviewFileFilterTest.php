<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai;

use DR\Review\Entity\Git\Diff\DiffBlock;
use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Service\Ai\AiCodeReviewFileFilter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(AiCodeReviewFileFilter::class)]
class AiCodeReviewFileFilterTest extends AbstractTestCase
{
    private AiCodeReviewFileFilter $filter;

    public function setUp(): void
    {
        parent::setUp();
        $this->filter = new AiCodeReviewFileFilter();
    }

    public function testInvokeShouldRejectFileWithBaselineInPath(): void
    {
        $file = new DiffFile();
        $file->filePathAfter = 'phpstan-baseline.neon';

        static::assertFalse(($this->filter)($file));
    }

    public function testInvokeShouldRejectFileWithDisallowedExtensionLock(): void
    {
        $file = new DiffFile();
        $file->filePathAfter = 'composer.lock';

        static::assertFalse(($this->filter)($file));
    }

    public function testInvokeShouldRejectFileWithDisallowedExtensionJson(): void
    {
        $file = new DiffFile();
        $file->filePathAfter = 'package.json';

        static::assertFalse(($this->filter)($file));
    }

    public function testInvokeShouldRejectBinaryFile(): void
    {
        $file = new DiffFile();
        $file->filePathAfter = 'image.png';
        $file->binary = true;

        static::assertFalse(($this->filter)($file));
    }

    public function testInvokeShouldRejectDeletedFile(): void
    {
        $file = new DiffFile();
        $file->filePathBefore = 'deleted-file.php';
        $file->filePathAfter = null;

        static::assertFalse(($this->filter)($file));
    }

    public function testInvokeShouldRejectFileWithMoreThan500Lines(): void
    {
        $file = new DiffFile();
        $file->filePathAfter = 'large-file.php';

        $block = new DiffBlock();
        for ($i = 0; $i < 501; $i++) {
            $block->lines[] = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line')]);
        }
        $file->addBlock($block);

        static::assertFalse(($this->filter)($file));
    }

    public function testInvokeShouldAcceptFileWith500Lines(): void
    {
        $file = new DiffFile();
        $file->filePathAfter = 'acceptable-file.php';

        $block = new DiffBlock();
        for ($i = 0; $i < 500; $i++) {
            $block->lines[] = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line')]);
        }
        $file->addBlock($block);

        static::assertTrue(($this->filter)($file));
    }

    public function testInvokeShouldAcceptValidFile(): void
    {
        $file = new DiffFile();
        $file->filePathAfter = 'src/Service/MyService.php';

        $block = new DiffBlock();
        $block->lines[] = new DiffLine(DiffLine::STATE_ADDED, [new DiffChange(DiffChange::ADDED, 'line')]);
        $file->addBlock($block);

        static::assertTrue(($this->filter)($file));
    }

    public function testInvokeShouldAcceptAddedFile(): void
    {
        $file = new DiffFile();
        $file->filePathBefore = null;
        $file->filePathAfter = 'src/NewFile.php';

        static::assertTrue(($this->filter)($file));
    }

    public function testInvokeShouldAcceptModifiedFile(): void
    {
        $file = new DiffFile();
        $file->filePathBefore = 'src/File.php';
        $file->filePathAfter = 'src/File.php';

        static::assertTrue(($this->filter)($file));
    }

    public function testInvokeShouldHandleUppercaseExtensions(): void
    {
        $file = new DiffFile();
        $file->filePathAfter = 'package.JSON';

        static::assertFalse(($this->filter)($file));
    }
}
