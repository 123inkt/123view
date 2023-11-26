<?php
declare(strict_types=1);

namespace DR\Review\Service\Git\Show;

use DR\Review\Entity\Git\Commit;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Git\Log\FormatPatternFactory;
use DR\Review\Service\Parser\GitLogParser;
use DR\Utils\Arrays;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class GitShowService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly GitCommandBuilderFactory $builderFactory,
        private readonly GitRepositoryService $repositoryService,
        private readonly GitLogParser $logParser,
        private readonly FormatPatternFactory $formatPatternFactory,
    ) {
    }

    /**
     * @throws Exception
     */
    public function getCommitFromHash(Repository $repository, string $commitHash): ?Commit
    {
        $commandBuilder = $this->builderFactory->createShow()
            ->startPoint($commitHash)
            ->noPatch()
            ->format($this->formatPatternFactory->createPattern());

        $output = $this->repositoryService->getRepository($repository)->execute($commandBuilder);

        // get first commit
        return Arrays::firstOrNull($this->logParser->parse($repository, $output));
    }

    /**
     * @throws RepositoryException
     */
    public function getFileContents(Revision $revision, string $file, bool $binary = false): string
    {
        $commandBuilder = $this->builderFactory->createShow()->file($revision->getCommitHash(), $file);
        if ($binary === true) {
            $commandBuilder->base64encode();
        }

        $output = $this->repositoryService->getRepository($revision->getRepository())->execute($commandBuilder);

        return $binary ? (string)base64_decode((string)preg_replace("/\s+/", "", $output), true) : $output;
    }
}
