<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DateTime;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Message\RevisionAddedMessage;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Service\Git\Log\FormatPatternFactory;
use DR\GitCommitNotification\Service\Parser\GitLogParser;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ImportReviewController
{
    public function __construct(
        private RepositoryRepository $repositoryRepository,
        private CacheableGitRepositoryService $gitRepository,
        private GitCommandBuilderFactory $commandBuilderFactory,
        private FormatPatternFactory $formatPatternFactory,
        private GitLogParser $logParser,
        private RevisionRepository $revisionRepository,
        private MessageBusInterface $bus
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/app/import-reviews', name: self::class, methods: 'GET')]
    public function __invoke(): Response
    {
        set_time_limit(0);
        ini_set('max_execution_time', 600);

        // get druid repository
        $repository = $this->repositoryRepository->findOneBy(['name' => 'drshop']);
        if ($repository === null) {
            return new JsonResponse(['no repository']);
        }

        // find the last revision
        $latestRevision = $this->revisionRepository->findOneBy(['repository' => $repository->getId()], ['createTimestamp' => 'DESC']);

        // build git log command
        $command = $this->commandBuilderFactory->createLog();
        $command->noMerges()
            ->remotes()
            ->reverse()
            ->format($this->formatPatternFactory->createPattern());
        if ($latestRevision !== null) {
            $command->since((new DateTime())->setTimestamp((int)$latestRevision->getCreateTimestamp()));
        }

        // get output
        $output = $this->gitRepository->getRepository((string)$repository->getUrl())->execute($command);

        // get commits
        $commits = $this->logParser->parse($repository, $output);

        // save
        $revisions = [];
        $count = 0;
        foreach ($commits as $commit) {
            $hash = (string)reset($commit->commitHashes);
            if ($latestRevision?->getCommitHash() === $hash) {
                continue;
            }

            $revision = new Revision();
            $revision->setRepository($repository);
            $revision->setAuthorEmail($commit->author->email);
            $revision->setAuthorName($commit->author->name);
            $revision->setCreateTimestamp($commit->date->getTimestamp());
            $revision->setCommitHash($hash);
            $revision->setTitle(mb_substr(trim($commit->getSubjectLine()), 0, 255));
            $latestRevision = $revision;

            $revisions[] = $revision;

            if (count($revisions) > 40) {
                $this->revisionRepository->save($revision, true);
                $this->dispatchRevisions($revisions);
                $revisions = [];
            } else {
                $this->revisionRepository->save($revision);
            }

            if (++$count > 10000) {
                break;
            }
        }

        $this->dispatchRevisions($revisions);

        return new JsonResponse($count);
    }

    /**
     * @param Revision[] $revisions
     */
    private function dispatchRevisions(array $revisions): void
    {
        foreach ($revisions as $revision) {
            $this->bus->dispatch(new RevisionAddedMessage($revision->getId()));
        }
    }
}
