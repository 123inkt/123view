<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use DR\Review\Entity\Git\Diff\DiffChange;
use DR\Review\Entity\Git\Diff\DiffLine;
use DR\Review\Model\Review\Highlight\HighlightedFile;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\HighlightFileViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(HighlightFileViewModel::class)]
class HighlightFileViewModelTest extends AbstractTestCase
{
    public function testGetLineAbsentHighlight(): void
    {
        $line  = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'code')]);
        $model = new HighlightFileViewModel(null);
        static::assertSame('code', $model->getLine(123, $line));
    }

    public function testGetLineHighlightDoesNotMatchCode(): void
    {
        $line      = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'code')]);
        $highlight = new HighlightedFile('file', static fn() => [122 => 'highlighted code']);
        $model     = new HighlightFileViewModel($highlight);

        static::assertSame('code', $model->getLine(123, $line));
    }

    public function testGetLineHighlightMatchesCode(): void
    {
        $line      = new DiffLine(DiffLine::STATE_UNCHANGED, [new DiffChange(DiffChange::UNCHANGED, 'code')]);
        $highlight = new HighlightedFile('file', static fn() => [122 => '<span>c&#x6f;de</span>']);
        $model     = new HighlightFileViewModel($highlight);

        static::assertSame('<span>c&#x6f;de</span>', $model->getLine(123, $line));
    }
}
