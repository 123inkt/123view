<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Url;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Project\ProjectsController;
use DR\Review\Repository\Url\ShortUrlRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class ShortUrlController extends AbstractController
{
    public function __construct(private readonly ShortUrlRepository $shortUrlRepository)
    {
    }

    #[Route('/url/{shortKey}', self::class, methods: 'GET')]
    public function __invoke(string $shortKey): RedirectResponse
    {
        $shortUrl = $this->shortUrlRepository->findOneBy(['shortKey' => $shortKey]);
        if ($shortUrl === null) {
            return $this->redirectToRoute(ProjectsController::class);
        }

        return $this->redirect((string)$shortUrl->getOriginalUrl());
    }
}
