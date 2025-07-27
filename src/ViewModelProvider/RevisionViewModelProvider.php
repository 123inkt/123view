<?php
declare(strict_types=1);

namespace DR\Review\ViewModelProvider;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Form\Review\Revision\DetachRevisionsFormType;
use DR\Review\Form\Review\Revision\RevisionVisibilityFormType;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Revision\RevisionFileRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Revision\RevisionVisibilityService;
use DR\Review\ViewModel\App\Review\PaginatorViewModel;
use DR\Review\ViewModel\App\Revision\ReviewRevisionViewModel;
use DR\Review\ViewModel\App\Revision\RevisionsViewModel;
use DR\Utils\Arrays;
use DR\Utils\Assert;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @implements ProviderInterface<RevisionsViewModel>
 */
// TODO remove non angular methods and implement only the provide method
// TODO rename to RevisionsViewModelProvider
class RevisionViewModelProvider implements ProviderInterface
{
    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly CodeReviewRepository $reviewRepository,
        private readonly RevisionRepository $revisionRepository,
        private readonly RevisionVisibilityService $visibilityService,
        private readonly RevisionFileRepository $revisionFileRepository,
        private readonly FormFactoryInterface $formFactory,
        private readonly User $user,
    ) {
    }

    public function getRevisionsViewModel(Repository $repository, int $page, string $searchQuery, ?bool $attached = null): RevisionsViewModel
    {
        $paginator = $this->revisionRepository->getPaginatorForSearchQuery((int)$repository->getId(), $page, $searchQuery, $attached);

        /** @var PaginatorViewModel<Revision> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($paginator, $page);

        return new RevisionsViewModel($repository, iterator_to_array($paginator), [], $paginatorViewModel, $searchQuery);
    }

    /**
     * @inheritDoc
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): RevisionsViewModel
    {
        $request      = Assert::isInstanceOf($context['request'], Request::class);
        $page         = $request->query->getInt('page', 1);
        $searchQuery  = trim($request->query->getString('search'));
        $attached     = null;
        $repositoryId = (int)Assert::numeric($uriVariables['repositoryId']);

        $repository = Assert::notNull($this->repositoryRepository->find($repositoryId), 'Repository not found ' . $repositoryId);
        $paginator  = $this->revisionRepository->getPaginatorForSearchQuery($repositoryId, $page, $searchQuery, $attached);
        $revisions  = iterator_to_array($paginator);

        // gather review ids
        $reviewIds = Arrays::removeNull(Arrays::map($revisions, static fn(Revision $rev) => $rev->getReview()?->getId()));
        $reviews   = Arrays::reindex($this->reviewRepository->findBy(['id' => $reviewIds]), static fn(CodeReview $review) => $review->getId());

        $reviewsByRevisionId = [];
        foreach ($revisions as $revision) {
            $reviewId = $revision->getReview()?->getId();
            if ($reviewId !== null && isset($reviews[$reviewId])) {
                $reviewsByRevisionId[$revision->getId()] = $reviews[$reviewId]->getProjectId();
            }
        }

        /** @var PaginatorViewModel<Revision> $paginatorViewModel */
        $paginatorViewModel = new PaginatorViewModel($paginator, $page);

        return new RevisionsViewModel($repository, $revisions, $reviewsByRevisionId, $paginatorViewModel);
    }

    /**
     * @param Revision[] $revisions
     */
    public function getRevisionViewModel(CodeReview $review, array $revisions): ReviewRevisionViewModel
    {
        $visibilities = $this->visibilityService->getRevisionVisibilities($review, $revisions, $this->user);
        $fileChanges  = $this->revisionFileRepository->getFileChanges($revisions);

        if ($review->getType() === CodeReviewType::COMMITS) {
            $detachRevisionForm = $this->formFactory
                ->create(DetachRevisionsFormType::class, null, ['reviewId' => $review->getId(), 'revisions' => $revisions])
                ->createView();
        } else {
            $detachRevisionForm = null;
        }
        $revisionVisibilityForm = $this->formFactory
            ->create(RevisionVisibilityFormType::class, ['visibilities' => $visibilities], ['reviewId' => $review->getId()])
            ->createView();

        return new ReviewRevisionViewModel($revisions, $fileChanges, $detachRevisionForm, $revisionVisibilityForm);
    }
}
