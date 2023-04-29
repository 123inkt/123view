<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\ViewModel\App\Review\CodeInspectionViewModel;

class CodeInspectionViewModelProvider
{
    public function __construct()
    {
    }

    public function getCodeInspectionViewModel(CodeReview $review, string $filePath): CodeInspectionViewModel
    {
    }
}
