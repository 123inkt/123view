<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Doctrine\Type\FrequencyType;
use DR\GitCommitNotification\Entity\Recipient;
use DR\GitCommitNotification\Entity\Repository;
use DR\GitCommitNotification\Entity\RepositoryProperty;
use DR\GitCommitNotification\Service\Config\ConfigLoader;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\Loader\ArrayLoader;

class ConfigLoadController extends AbstractController
{
    /**
     * @throws Exception
     */
    #[Route('/load-repositories', self::class)]
    public function __invoke(ManagerRegistry $doctrine, ConfigLoader $loader): Response
    {
        $em     = $doctrine->getManager();
        $config = $loader->load(FrequencyType::ONCE_PER_HOUR, new ArrayInput([]));

        foreach ($config->repositories->getRepositories() as $repository) {
            $dbRepository = new Repository();
            $dbRepository->setName($repository->name);
            $dbRepository->setUrl($repository->url);

            if ($repository->gitlabProjectId !== null) {
                $property = new RepositoryProperty();
                $property->setName('gitlab-project-id');
                $property->setValue((string)$repository->gitlabProjectId);
                $dbRepository->addRepositoryProperty($property);
            }

            if ($repository->upsourceProjectId !== null) {
                $property = new RepositoryProperty();
                $property->setName('upsource-project-id');
                $property->setValue($repository->upsourceProjectId);
                $dbRepository->addRepositoryProperty($property);
            }

            $em->persist($dbRepository);
        }

        $em->flush();

        return new JsonResponse('done');
    }
}
