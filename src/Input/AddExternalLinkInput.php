<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Input;

use DigitalRevolution\SymfonyConsoleValidation\AbstractValidatedInput;
use DigitalRevolution\SymfonyConsoleValidation\ValidationRules;

class AddExternalLinkInput extends AbstractValidatedInput
{
    public function getPattern(): string
    {
        /** @var string $pattern */
        $pattern = $this->input->getArgument('pattern');

        return $pattern;
    }

    public function getUrl(): string
    {
        /** @var string $url */
        $url = $this->input->getArgument('url');

        return $url;
    }

    public function getValidationRules(): ValidationRules
    {
        return (new ValidationRules())
            ->addArgumentConstraint('pattern', 'required|filled|string')
            ->addArgumentConstraint('url', 'required|string|regex:#^https?://#');
    }
}
