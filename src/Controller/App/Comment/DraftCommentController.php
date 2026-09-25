<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModelProvider\DraftCommentViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DraftCommentController extends AbstractController
{
    public function __construct(private readonly DraftCommentViewModelProvider $viewModelProvider)
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

        return [
            'page_title' => 'draft.comments.overview',
            'viewModel'  => $this->viewModelProvider->getDraftCommentsViewModel($user, $page)
        ];
    }
}
