<?php
declare(strict_types=1);

namespace DR\Review\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Review\ViewModel\App\Review\ReviewViewModel;

class ReviewRequest extends AbstractValidatedRequest
{
    public function getFilePath(): ?string
    {
        return $this->request->query->get('filePath');
    }

    public function getTab(): string
    {
        return $this->request->query->get('tab', ReviewViewModel::SIDEBAR_TAB_OVERVIEW);
    }

    public function getDiffMode(): ReviewDiffModeEnum
    {
        return ReviewDiffModeEnum::from($this->request->query->get('diff', ReviewDiffModeEnum::INLINE->value));
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'query' => [
                    'filePath' => 'string|filled',
                    'tab'      => 'string|in:' . ReviewViewModel::SIDEBAR_TAB_REVISIONS . ',' . ReviewViewModel::SIDEBAR_TAB_OVERVIEW,
                    'diff'     => 'string|in:' . implode(',', ReviewDiffModeEnum::values())
                ]
            ]
        );
    }
}
