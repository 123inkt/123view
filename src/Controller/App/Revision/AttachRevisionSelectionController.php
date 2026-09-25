<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Revision;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Revision\AttachRevisionsViewModel;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;
use DR\Utils\Assert;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AttachRevisionSelectionController
{
    public function __construct(private readonly RevisionViewModelProvider $revisionViewModelProvider)
    {
    }

    /**
     * @return array<string, AttachRevisionsViewModel>
     */
    #[Route('app/reviews/{id<\d+>}/attach-revisions', name: self::class, methods: 'GET')]
    #[Template('app/revision/revisions.attach.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] CodeReview $review): array
    {
        $searchQuery = trim($request->query->get('search', ''));
        $page        = $request->query->getInt('page', 1);

        $revisionsViewModel = $this->revisionViewModelProvider->getRevisionsViewModel(
            Assert::notNull($review->getRepository()),
            $page,
            $searchQuery,
            false
        );

        return ['attachRevisionsModel' => new AttachRevisionsViewModel($review, $revisionsViewModel)];
    }
}
