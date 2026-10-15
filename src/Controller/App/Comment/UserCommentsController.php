<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Comment;

use Doctrine\DBAL\Exception as DBALException;
use DR\Review\Controller\AbstractController;
use DR\Review\Request\Comment\UserCommentsRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Comment\UserCommentsViewModel;
use DR\Review\ViewModelProvider\UserCommentsViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserCommentsController extends AbstractController
{
    public function __construct(private readonly TranslatorInterface $translator, private readonly UserCommentsViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array{page_title: string, viewModel: UserCommentsViewModel}
     * @throws DBALException
     */
    #[Route('app/comments', name: self::class, methods: 'GET')]
    #[Template('app/comment/user_comments.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(UserCommentsRequest $request): array
    {
        return [
            'page_title' => $this->translator->trans('my.comments'),
            'viewModel'  => $this->viewModelProvider->getUserCommentsViewModel(
                $this->getUser(),
                $request->getSearchQuery(),
                $request->getOrderBy(),
                $request->getPage()
            )
        ];
    }
}
