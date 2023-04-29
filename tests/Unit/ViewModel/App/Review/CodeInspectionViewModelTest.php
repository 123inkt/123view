<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Review;

use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\CodeInspectionViewModel;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeInspectionViewModel::class)]
class CodeInspectionViewModelTest extends AbstractTestCase
{
    public function testGetIssues(): void
    {
        $issue = new CodeInspectionIssue();
        $issue->setLineNumber(500);

        $viewModel = new CodeInspectionViewModel([$issue]);

        static::assertSame([], $viewModel->getIssues(400));
        static::assertSame([$issue], $viewModel->getIssues(500));
    }
}
