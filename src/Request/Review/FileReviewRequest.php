<?php
declare(strict_types=1);

namespace DR\Review\Request\Review;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Security\SessionKeys;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use DR\Utils\Assert;

class FileReviewRequest extends AbstractValidatedRequest
{
    public function getFilePath(): string
    {
        return $this->request->query->get('filePath', '');
    }

    public function getComparisonPolicy(): DiffComparePolicy
    {
        $policy = $this->request->hasSession() ? $this->request->getSession()->get(SessionKeys::DIFF_COMPARISON_POLICY->value) : null;
        if ($policy === null) {
            return DiffComparePolicy::ALL;
        }

        return DiffComparePolicy::from(Assert::string($policy));
    }

    public function getDiffMode(): ReviewDiffModeEnum
    {
        $mode = $this->request->hasSession() ? $this->request->getSession()->get(SessionKeys::REVIEW_DIFF_MODE->value) : null;
        if ($mode === null) {
            return ReviewDiffModeEnum::INLINE;
        }

        return ReviewDiffModeEnum::from(Assert::string($mode));
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(['query' => ['filePath' => 'required|string|filled']]);
    }
}
