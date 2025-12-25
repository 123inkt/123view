<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Grep;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryService;
use Exception;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GitGrepService
{
    public function __construct(private readonly GitCommandBuilderFactory $builderFactory, private readonly GitRepositoryService $repositoryService)
    {
    }

    /**
     * @throws Exception
     */
    public function grep(Revision $revision, string $pattern, ?int $context = null): ?string
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

        try {
            return $this->repositoryService->getRepository($revision->getRepository())->execute($commandBuilder);
        } catch (ProcessFailedException $exception) {
            // if git grep has no match, will exit with error code
            $process = $exception->getProcess();
            if (trim($process->getErrorOutput()) === '' && trim($process->getOutput()) === '') {
                return null;
            }
            throw $exception;
        }
    }
}
