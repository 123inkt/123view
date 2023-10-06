<?php
declare(strict_types=1);

namespace DR\Review\Service\Revision;

use DR\Review\Entity\Notification\Rule;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\Fetch\GitFetchRemoteRevisionService;
use DR\Review\Utility\Batch;
use DR\Utils\Arrays;
use Exception;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class RevisionFetchService
{
    use LoggerAwareTrait;

    public function __construct(
        private GitFetchRemoteRevisionService $remoteRevisionService,
        private RevisionRepository $revisionRepository,
        private RevisionFactory $revisionFactory,
        private MessageBusInterface $bus,
    ) {
    }

    /**
     * @param Rule[] $rules
     *
     * @throws Exception
     */
    public function fetchRevisionsForRules(array $rules): void
    {
        $repositories = array_map(static fn(Rule $rule) => $rule->getRepositories()->toArray(), $rules);
        $repositories = count($repositories) > 0 ? Arrays::unique(array_merge(...$repositories)) : [];
        foreach ($repositories as $repository) {
            $this->fetchRevisions($repository);
        }
    }

    /**
     * @throws Exception
     */
    public function fetchRevisions(Repository $repository): void
    {
        // setup batch to save revisions
        $batch = new Batch(
            500,
            function (array $revisions) use ($repository): void {
                $this->logger?->info("RevisionFetchService: {revisions} new revisions", ['revisions' => count($revisions)]);
                $revisions = $this->revisionRepository->saveAll($repository, $revisions);
                $this->dispatchRevisions($revisions);
            }
        );

        $revisions = [];
        foreach ($this->remoteRevisionService->fetchRevisionFromRemote($repository) as $commit) {
            foreach ($this->revisionFactory->createFromCommit($commit) as $revision) {
                // ensure no duplicate revisions are added
                if (isset($revisions[$revision->getCommitHash()])) {
                    continue;
                }
                $batch->add($revision);
                $revisions[$revision->getCommitHash()] = true;
            }
        }
        $batch->flush();
    }

    /**
     * @param Revision[] $revisions
     */
    private function dispatchRevisions(array $revisions): void
    {
        foreach ($revisions as $revision) {
            $this->bus->dispatch(new NewRevisionMessage((int)$revision->getId()));
        }
    }
}
