<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Input;

use DigitalRevolution\SymfonyConsoleValidation\AbstractValidatedInput;
use DigitalRevolution\SymfonyConsoleValidation\ValidationRules;

class AddRepositoryInput extends AbstractValidatedInput
{
    public function getRepository(): string
    {
        /** @var string $repository */
        $repository = $this->input->getArgument('repository');

        return $repository;
    }

    public function getName(): ?string
    {
        /** @var ?string $name */
        $name = $this->input->getOption('name');

        // try to retrieve name from repository url
        if (is_string($name) === false && preg_match('#/([^/]+?)(?:.git)?$#i', $this->getRepository(), $matches) === 1) {
            $name = $matches[1];
        }

        return $name;
    }

    public function getUpsourceId(): ?string
    {
        /** @var ?string $upsourceId */
        $upsourceId = $this->input->getOption('upsource');

        return $upsourceId;
    }

    public function getGitlabId(): ?int
    {
        /** @var ?string $gitlabId */
        $gitlabId = $this->input->getOption('gitlab');

        return $gitlabId === null ? null : (int)$gitlabId;
    }

    public function getValidationRules(): ValidationRules
    {
        return (new ValidationRules())
            ->addArgumentConstraint('repository', 'required|filled|string')
            ->addOptionConstraint('name', 'filled|string')
            ->addOptionConstraint('upsource', 'filled|string')
            ->addOptionConstraint('gitlab', 'int|min:1');
    }
}
