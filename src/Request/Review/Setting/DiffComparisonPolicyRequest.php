<?php
declare(strict_types=1);

namespace DR\Review\Request\Review\Setting;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;

class DiffComparisonPolicyRequest extends AbstractValidatedRequest
{
    public function getComparisonPolicy(): DiffComparePolicy
    {
        return DiffComparePolicy::from($this->request->request->getString('comparisonPolicy'));
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(['request' => ['comparisonPolicy' => 'required|string|in:' . implode(',', DiffComparePolicy::values())]]);
    }
}
