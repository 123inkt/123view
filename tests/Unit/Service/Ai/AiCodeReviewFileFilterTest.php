<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Service\Ai\AiCodeReviewFileFilter;
use DR\Review\Service\Ai\AiCodeReviewFileSkipReason;
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

    public function testGetSkipReasonShouldReturnIrrelevantForBaselineFile(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'test/baseline/foo.php';

        static::assertSame(AiCodeReviewFileSkipReason::Irrelevant, $this->filter->getSkipReason($file));
    }

    public function testGetSkipReasonShouldReturnIrrelevantForDisallowedExtension(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'composer.lock';

        static::assertSame(AiCodeReviewFileSkipReason::Irrelevant, $this->filter->getSkipReason($file));
    }

    public function testGetSkipReasonShouldReturnIrrelevantForJsonExtension(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'package.json';

        static::assertSame(AiCodeReviewFileSkipReason::Irrelevant, $this->filter->getSkipReason($file));
    }

    public function testGetSkipReasonShouldReturnIrrelevantForBinaryFile(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'image.png';
        $file->binary        = true;

        static::assertSame(AiCodeReviewFileSkipReason::Irrelevant, $this->filter->getSkipReason($file));
    }

    public function testGetSkipReasonShouldReturnIrrelevantForDeletedFile(): void
    {
        $file                 = new DiffFile();
        $file->filePathBefore = 'src/Foo.php';

        static::assertSame(AiCodeReviewFileSkipReason::Irrelevant, $this->filter->getSkipReason($file));
    }

    public function testGetSkipReasonShouldReturnTooManyLinesWhenLineCountExceedsLimit(): void
    {
        $file                = $this->createMock(DiffFile::class);
        $file->filePathAfter = 'src/Foo.php';
        $file->raw           = 'small diff';
        $file->expects($this->once())->method('getLines')->willReturn(array_fill(0, AiCodeReviewFileFilter::MAX_FILE_LINES + 1, 'line'));

        static::assertSame(AiCodeReviewFileSkipReason::TooManyLines, $this->filter->getSkipReason($file));
    }

    public function testGetSkipReasonShouldReturnNullAtExactLineLimit(): void
    {
        $file                = $this->createMock(DiffFile::class);
        $file->filePathAfter = 'src/Foo.php';
        $file->raw           = 'small diff';
        $file->expects($this->once())->method('getLines')->willReturn(array_fill(0, AiCodeReviewFileFilter::MAX_FILE_LINES, 'line'));

        static::assertNull($this->filter->getSkipReason($file));
    }

    public function testGetSkipReasonShouldReturnTooLargeWhenDiffCharactersExceedBudget(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'src/Foo.php';
        $file->raw           = str_repeat('x', AiCodeReviewFileFilter::MAX_DIFF_CHARACTERS + 1);

        static::assertSame(AiCodeReviewFileSkipReason::TooLarge, $this->filter->getSkipReason($file));
    }

    public function testGetSkipReasonShouldReturnNullForAcceptedFile(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'src/Foo.php';
        $file->raw           = 'small diff';

        static::assertNull($this->filter->getSkipReason($file));
    }

    public function testInvokeShouldReturnFalseWhenSkipped(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'composer.lock';

        static::assertFalse(($this->filter)($file));
    }

    public function testInvokeShouldReturnTrueWhenAccepted(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = 'src/Foo.php';
        $file->raw           = 'small diff';

        static::assertTrue(($this->filter)($file));
    }
}
