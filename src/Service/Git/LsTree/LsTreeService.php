<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\LsTree;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Utils\Assert;
use Exception;

class LsTreeService
{
    public function __construct(private readonly GitCommandBuilderFactory $builderFactory, private readonly GitRepositoryService $repositoryService)
    {
    }

    /**
     * @return string[]
     * @throws Exception
     */
    public function listFiles(Revision $revision, string $filepath): array
    {
        $filepath = ltrim($filepath, '/');
        $glob     = str_contains($filepath, '*');

        $commandBuilder = $this->builderFactory->createLsTree()
            ->nameOnly()
            ->hash($revision->getCommitHash());

        if ($glob) {
            // scan all files up to the glob pattern
            $commandBuilder->file(substr($filepath, 0, Assert::notFalse(strpos($filepath, '*'))));
            $commandBuilder->recursive();
        } else {
            $commandBuilder->file($filepath);
        }

        $output = $this->repositoryService->getRepository($revision->getRepository())->execute($commandBuilder);

        // parse output
        $files = array_map('trim', explode("\n", $output));
        $files = array_filter($files, static fn(string $line): bool => $line !== '');

        if ($glob) {
            // convert glob pattern to regex
            $regex = str_replace(['\*\*', '\*'], ['.*', '[^\\\\]*'], preg_quote($filepath, '#'));
            $files = array_filter($files, static fn(string $line): bool => preg_match('#^' . $regex . '$#', $line) === 1);
        }

        return $files;
    }
}
