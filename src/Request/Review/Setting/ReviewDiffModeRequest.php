<?php
declare(strict_types=1);

namespace DR\Review\Request\Review\Setting;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;

class ReviewDiffModeRequest extends AbstractValidatedRequest
{
    public function getDiffMode(): ReviewDiffModeEnum
    {
        return ReviewDiffModeEnum::from($this->request->request->getString('diffMode'));
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(['request' => ['diffMode' => 'required|string|in:' . implode(',', ReviewDiffModeEnum::values())]]);
    }
}
