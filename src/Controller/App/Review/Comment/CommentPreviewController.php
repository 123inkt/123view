<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review\Comment;

use DR\GitCommitNotification\Request\Comment\CommentPreviewRequest;
use DR\GitCommitNotification\Service\Markdown\MarkdownService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CommentPreviewController
{
    public function __construct(private readonly MarkdownService $markdownService)
    {
    }

    #[Route('app/reviews/comment/markdown', name: self::class, methods: 'GET')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]

    public function __invoke(CommentPreviewRequest $request): Response
    {
        return (new Response($this->markdownService->convert($request->getMessage()), 200, ['Content-Type' => 'text/html']))
            ->setMaxAge(86400)
            ->setPublic();
    }
}
