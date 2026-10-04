<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Request\Comment\CommentOverviewRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModelProvider\CommentOverviewViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class CommentOverviewController extends AbstractController
{
    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly CommentOverviewViewModelProvider $viewModelProvider
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    #[Route('app/comments', name: self::class, methods: 'GET')]
    #[Template('app/comment/overview.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(CommentOverviewRequest $request): array
    {
        $user = $this->getUser();

        return [
            'page_title' => $this->translator->trans('comments.overview'),
            'viewModel'  => $this->viewModelProvider->getCommentOverviewViewModel($user, $request)
        ];
    }
}
