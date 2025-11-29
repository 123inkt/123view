<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Grep;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryService;
use Exception;

class GitGrepService
{
    public function __construct(private readonly GitCommandBuilderFactory $builderFactory, private readonly GitRepositoryService $repositoryService)
    {
    }

    /**
     * @throws Exception
     */
    public function grep(Revision $revision, string $pattern, ?int $context = null): string
    {
        $commandBuilder = $this->builderFactory->createGrep()
            ->pattern($pattern)
            ->hash($revision->getCommitHash())
            ->fullName()
            ->noColor()
            ->lineNumber();

        if ($context !== null) {
            $commandBuilder->context($context);
        }

        return $this->repositoryService->getRepository($revision->getRepository())->execute($commandBuilder);
    }
}
