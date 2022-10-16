<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Message\RevisionAddedMessage;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Git\Log\GitLogService;
use DR\GitCommitNotification\Service\Revision\RevisionFactory;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ImportReviewController
{
    public function __construct(
        private RepositoryRepository $repositoryRepository,
        private GitLogService $logService,
        private RevisionRepository $revisionRepository,
        private RevisionFactory $revisionFactory,
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
        $maxResults = 10000;

        // get drshop repository
        $repository = $this->repositoryRepository->findOneBy(['name' => 'drshop']);
        if ($repository === null) {
            return new JsonResponse(['no repository']);
        }

        // find the last revision
        $latestRevision = $this->revisionRepository->findOneBy(['repository' => $repository->getId()], ['createTimestamp' => 'DESC']);

        // build git log command
        $commits = $this->logService->getCommitsSince($repository, $latestRevision, $maxResults);

        // chunk it
        $commitChunks = array_chunk($commits, 50);

        // save
        $count = 0;
        foreach ($commitChunks as $commitChunk) {
            $revisions = [];

            foreach ($commitChunk as $commit) {
                foreach ($this->revisionFactory->createFromCommit($repository, $commit) as $revision) {
                    $this->revisionRepository->save($revision);
                    $revisions[] = $revision;
                    ++$count;
                }
            }

            $this->revisionRepository->flush();
            $this->dispatchRevisions($revisions);
        }

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
