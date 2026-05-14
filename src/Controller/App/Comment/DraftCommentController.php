<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Comment\DraftCommentsViewModel;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Utils\Arrays;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DraftCommentController extends AbstractController
{
    private const PAGE_SIZE = 30;

    public function __construct(private readonly CommentRepository $commentRepository)
    {
    }

    /**
     * @return array<string, mixed>
     */
    #[Route('app/comments/drafts', name: self::class, methods: 'GET')]
    #[Template('app/comment/draft-overview.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): array
    {
        $user = $this->getUser();
        $page = max(1, $request->query->getInt('page', 1));

        $commentPaginator = $this->commentRepository->getDraftsByUser($user, $page, self::PAGE_SIZE);
        $comments         = Arrays::reindex($commentPaginator, static fn(Comment $comment) => (int)$comment->getId());
        $commentsGrouped  = Arrays::groupBy($comments, static fn(Comment $comment) => $comment->getReview()->getId());
        $reviews          = Arrays::mapAssoc($comments, static fn(Comment $comment) => [$comment->getReview()->getId(), $comment->getReview()]);

        /** @var PaginatorViewModel<Comment> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($commentPaginator, $page);
        $viewModel          = new DraftCommentsViewModel($commentsGrouped, $reviews, $paginatorViewModel);

        return ['page_title' => 'draft.comments.overview', 'viewModel' => $viewModel];
    }
}
