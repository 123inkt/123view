<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Recipient;
use DR\GitCommitNotification\Entity\Repository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AuthenticationController extends AbstractController
{
    #[Route('/', self::class)]
    public function __invoke(ManagerRegistry $doctrine): Response
    {
        $recipient = $doctrine->getRepository(Recipient::class)->find(1);
        $repositories = $doctrine->getRepository(Repository::class)->findAll();

        $result = [];

        if ($recipient !== null) {
            $result['recipient'] = ['name' => $recipient->getName(), 'email' => $recipient->getEmail()];
        }

        foreach ($repositories as $repository) {
            $result['repository'][] = ['repository' => $repository->getName()];
        }

        return new JsonResponse($result);
    }
}
