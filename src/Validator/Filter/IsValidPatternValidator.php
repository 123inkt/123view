<?php
declare(strict_types=1);

namespace DR\Review\Validator\Filter;

use DR\Review\Doctrine\Type\FilterType;
use DR\Review\Entity\Notification\Filter;
use RuntimeException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class IsValidPatternValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate(mixed $value, Constraint $constraint): bool
    {
        if ($value instanceof Filter === false) {
            throw new RuntimeException('Only type Filter is valid');
        }

        switch ($value->getType()) {
            // pattern should be valid e-mail
            case FilterType::AUTHOR:
                if (preg_match('/^.+@\S+\.\S+$/', $value->getPattern()) === 1) {
                    return true;
                }

                $this->context->buildViolation(IsValidPattern::MESSAGE_EMAIL)
                    ->atPath('pattern')
                    ->addViolation();

                return false;

            // pattern should be valid regex
            case FilterType::SUBJECT:
            case FilterType::FILE:
                // validating regex, suppress any warnings preg_match gives.
                if (@preg_match($value->getPattern(), '') !== false) {
                    return true;
                }

                $this->context->buildViolation(IsValidPattern::MESSAGE_REGEX)
                    ->atPath('pattern')
                    ->addViolation();

                return false;

            default:
                throw new RuntimeException('Invalid filter type: ' . $value->getType());
        }
    }
}
