<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Revision;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Security\Role\Roles;
use DR\Review\Utility\Assert;
use DR\Review\ViewModel\App\Revision\AttachRevisionsViewModel;
use DR\Review\ViewModelProvider\RevisionViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Entity;
use Symfony\Bridge\Twig\Attribute\IsGranted;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

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
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): array
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
