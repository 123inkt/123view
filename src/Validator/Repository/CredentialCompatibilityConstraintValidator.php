<?php
declare(strict_types=1);

namespace DR\Review\Validator\Repository;

use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Entity\Repository\Repository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class CredentialCompatibilityConstraintValidator extends ConstraintValidator
{
    /**
     * @inheritDoc
     */
    public function validate(mixed $value, Constraint $constraint): void
    {
        if ($constraint instanceof CredentialCompatibilityConstraint === false) {
            throw new UnexpectedTypeException($constraint, CredentialCompatibilityConstraint::class);
        }

        if ($value instanceof Repository === false) {
            return;
        }

        if ($value->hasUrl() === false) {
            // URL validation handles missing URLs.
            return;
        }

        $scheme     = $value->getUrl()->getScheme();
        $credential = $value->getCredential();

        if ($scheme === 'ssh') {
            // SSH URLs require an SSH-key credential.
            if ($credential === null || $credential->getAuthType() !== AuthenticationType::SSH_KEY) {
                $this->context
                    ->buildViolation($constraint->messageSshRequiresSshKey)
                    ->atPath('credential')
                    ->addViolation();
            }

            return;
        }

        // HTTP(S) URLs must not use an SSH-key credential.
        if ($credential !== null && $credential->getAuthType() === AuthenticationType::SSH_KEY) {
            $this->context
                ->buildViolation($constraint->messageHttpForbidsSshKey)
                ->atPath('credential')
                ->addViolation();
        }
    }
}
