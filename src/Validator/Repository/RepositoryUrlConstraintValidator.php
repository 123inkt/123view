<?php
declare(strict_types=1);

namespace DR\Review\Validator\Repository;

use League\Uri\Uri;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Throwable;

class RepositoryUrlConstraintValidator extends ConstraintValidator
{
    private const ALLOWED_SCHEMES = ['http', 'https', 'ssh'];

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint instanceof RepositoryUrlConstraint === false) {
            throw new UnexpectedTypeException($constraint, RepositoryUrlConstraint::class);
        }

        if (is_string($value) === false || $value === '') {
            // NotBlank handles the empty-value case; non-strings are not for this constraint.
            return;
        }

        // Normalise SCP-style input before parsing so the scheme is available.
        $normalized = $this->normalizeScp($value);

        try {
            $uri = Uri::new($normalized);
        } catch (Throwable) {
            $this->context->buildViolation($constraint->messageInvalidUrl)->addViolation();

            return;
        }

        $this->validateScheme($uri->getScheme(), $uri->getUserInfo(), $constraint);
    }

    private function validateScheme(?string $scheme, ?string $userInfo, RepositoryUrlConstraint $constraint): void
    {
        if ($scheme === null || in_array($scheme, self::ALLOWED_SCHEMES, true) === false) {
            $this->context->buildViolation($constraint->messageUnsupportedScheme)->addViolation();

            return;
        }

        if ($scheme === 'ssh' && ($userInfo === null || $userInfo === '')) {
            $this->context->buildViolation($constraint->messageSshRequiresUser)->addViolation();
        }
    }

    /**
     * Convert SCP-style SSH notation (user@host:path) to a canonical ssh:// URI string.
     * Returns the original string unchanged when it does not match the SCP pattern.
     */
    private function normalizeScp(string $value): string
    {
        if (preg_match('/^([^@\/:]+@[^\/:]+):([^\/].*)$/', $value, $matches) === 1) {
            return 'ssh://' . $matches[1] . '/' . $matches[2];
        }

        return $value;
    }
}
