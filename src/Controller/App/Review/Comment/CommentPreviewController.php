<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Request\Comment\CommentPreviewRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class CommentPreviewController extends AbstractController
{
    public function __construct(private readonly CommentMentionService $mentionService, private readonly MarkdownConverter $converter)
    {
    }

    #[Route('app/reviews/comment/markdown', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(CommentPreviewRequest $request): Response
    {
        $message = $request->getMessage();
        $message = $this->mentionService->replaceMentionedUsers($message, $this->mentionService->getMentionedUsers($message));
        $message = $this->converter->convert($message)->getContent();

        return (new Response($message, 200, ['Content-Type' => 'text/html']))
            ->setMaxAge(86400)
            ->setPublic();
    }
}
