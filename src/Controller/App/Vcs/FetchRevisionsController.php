<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Vcs;

use DR\Review\Message\Revision\FetchRepositoryRevisionsMessage;
use DR\Review\Repository\Config\RepositoryRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

class FetchRevisionsController
{
    public function __construct(private readonly RepositoryRepository $repositoryRepository, private readonly MessageBusInterface $bus)
    {
    }

    #[Route('/~vcs/{repositoryIdentifier<[\w-]+>}', self::class, methods: ['GET', 'POST'])]
    public function __invoke(string $repositoryIdentifier): Response
    {
        if (preg_match('/^\d+$/', $repositoryIdentifier) === 1) {
            // if identifier is int, find repository by id
            $repository = $this->repositoryRepository->find((int)$repositoryIdentifier);
        } else {
            // else by name
            $repository = $this->repositoryRepository->findOneBy(['name' => $repositoryIdentifier]);
        }

        if ($repository === null) {
            return new Response('Rejected', Response::HTTP_BAD_REQUEST);
        }

        $this->bus->dispatch(new FetchRepositoryRevisionsMessage((int)$repository->getId()));

        return new Response('Accepted');
    }
}
