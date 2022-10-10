<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DateTime;
use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\Log\FormatPatternFactory;
use DR\GitCommitNotification\Service\Git\Log\GitLogCommandBuilderFactory;
use DR\GitCommitNotification\Service\Parser\GitLogParser;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportReviewController
{
    public function __construct(
        private RepositoryRepository $repositoryRepository,
        private CacheableGitRepositoryService $gitRepository,
        private GitLogCommandBuilderFactory $commandBuilderFactory,
        private FormatPatternFactory $formatPatternFactory,
        private GitLogParser $logParser,
        private RevisionRepository $revisionRepository,
        private ManagerRegistry $registry
    ) {
    }

    /**
     * @throws Exception
     */
    #[Route(path: '/app/import-reviews', name: self::class, methods: 'GET')]
    public function __invoke(): Response
    {
        // get druid repository
        $repository = $this->repositoryRepository->findOneBy(['name' => 'drcore']);
        if ($repository === null) {
            return new JsonResponse(['no repository']);
        }

        // find the last revision
        $latestRevision = $this->revisionRepository->findOneBy(['repository' => $repository->getId()], ['createTimestamp' => 'DESC']);

        // build git log command
        $command = $this->commandBuilderFactory->create();
        $command->noMerges()
            ->remotes()
            ->reverse()
            ->format($this->formatPatternFactory->createPattern());
        if ($latestRevision !== null) {
            $command->since((new DateTime())->setTimestamp($latestRevision->getCreateTimestamp()));
        }

        // get output
        $output = $this->gitRepository->getRepository($repository->getUrl())->execute($command);

        // get commits
        $commits = $this->logParser->parse($repository, $output);

        // save
        $doctrine = $this->registry->getManager();
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
            $doctrine->persist($revision);

            if ($count++ > 1000) {
                $count = 0;
                $doctrine->flush();
            }
        }
        $doctrine->flush();

        return new JsonResponse($commits);
    }
}
