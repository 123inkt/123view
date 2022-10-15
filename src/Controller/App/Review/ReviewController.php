<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Form\Review\AddReviewerFormType;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Service\Git\GitCodeReviewDiffService;
use DR\GitCommitNotification\Utility\Type;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class ReviewController extends AbstractController
{
    public function __construct(private readonly ExternalLinkRepository $linkRepository, private readonly GitCodeReviewDiffService $diffService)
    {
    }

    /**
     * @return array<string, object>
     * @throws Throwable
     */
    #[Route('app/reviews/{id<\d+>}', name: self::class, methods: 'GET')]
    #[Template('app/review/review.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): array
    {
        $addReviewerForm = $this->createForm(AddReviewerFormType::class, null, ['review' => $review])->createView();

        $revisions = $review->getRevisions()->toArray();
        $files     = $this->diffService->getDiffFiles($revisions);

        $selectedFile = null;
        $filePath     = $request->query->get('filePath');
        foreach ($files as $file) {
            if ($file->getFile()?->getPathname() === $filePath) {
                $selectedFile = $file;
            }
        }

        $selectedFile ??= Type::notFalse(reset($files));

        $links = $this->linkRepository->findAll();

        return ['reviewModel' => new ReviewViewModel($review, $files, $selectedFile, $addReviewerForm, $links)];
    }
}
