<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Service\Git\GitCodeReviewDiffService;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class ReviewController
{
    public function __construct(private readonly GitCodeReviewDiffService $diffService)
    {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}', name: self::class, methods: 'GET')]
    #[Template('app/review/review.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(CodeReview $review): array
    {
        $revisions = $review->getRevisions()->toArray();
        $files     = $this->diffService->getDiffFiles($revisions);

        /** @var DiffFile $file */
        $file = reset($files);

        return ['reviewModel' => new ReviewViewModel($review, $files, $file)];
    }
}
