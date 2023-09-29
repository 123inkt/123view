<?php

declare(strict_types=1);

namespace DR\Review\MessageHandler;

use DR\Review\Message\Revision\ValidateRevisionsMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\Revision\RevisionValidationService;
use DR\Utils\Assert;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

class ValidateRevisionsMessageHandler
{
    public function __construct(private readonly RepositoryRepository $repository, private readonly RevisionValidationService $validationService)
    {
    }

    #[AsMessageHandler(fromTransport: 'async_revisions')]
    public function __invoke(ValidateRevisionsMessage $event): void
    {
        $this->validationService->validate(Assert::notNull($this->repository->find($event->repositoryId)));
    }
}
