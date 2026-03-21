<?php
declare(strict_types=1);

namespace DR\Review\Request\Review\Setting;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;

class DiffVisibleLinesRequest extends AbstractValidatedRequest
{
    public function getVisibleLines(): int
    {
        return $this->request->request->getInt('visibleLines');
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(['request' => ['visibleLines' => 'required|int|min:0|max:20']]);
    }
}
