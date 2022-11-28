<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Revision;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Utility\Assert;
use DR\GitCommitNotification\ViewModel\App\Review\AttachRevisionsViewModel;
use DR\GitCommitNotification\ViewModelProvider\RevisionViewModelProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
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
    #[IsGranted('ROLE_USER')]
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
