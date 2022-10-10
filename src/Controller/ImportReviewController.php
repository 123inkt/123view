<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
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
        $repository = $this->repositoryRepository->findOneBy(['name' => 'Druid']);
        if ($repository === null) {
            return new JsonResponse(['no repository']);
        }

        $command = $this->commandBuilderFactory->create();
        $command->noMerges()->remotes()->reverse()->format($this->formatPatternFactory->createPattern());

        $output = $this->gitRepository->getRepository($repository->getUrl())->execute($command);

        $commits = $this->logParser->parse($repository, $output);

        $doctrine = $this->registry->getManager();
        foreach ($commits as $commit) {
            $revision = new Revision();
            $revision->setRepository($repository);
            $revision->setAuthorEmail($commit->author->email);
            $revision->setAuthorName($commit->author->name);
            $revision->setCommitHash((string)reset($commit->commitHashes));
            $revision->setTitle(trim($commit->getSubjectLine()));
            $doctrine->persist($revision);
        }
        $doctrine->flush();

        return new JsonResponse($commits);
    }
}
