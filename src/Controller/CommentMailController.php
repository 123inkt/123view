<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService;
use DR\GitCommitNotification\Utility\Type;
use DR\GitCommitNotification\ViewModel\Mail\NewCommentViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class CommentMailController extends AbstractController
{
    public function __construct(
        private readonly ReviewDiffService $diffService,
        private readonly DiffFinder $diffFinder
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/mail/comment/{id<\d+>}', name: self::class, methods: 'GET')]
    #[Template('mail/mail.comment.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('comment')]
    public function __invoke(Comment $comment): array
    {
        /** @var CodeReview $review */
        $review = $comment->getReview();
        /** @var LineReference $lineReference */
        $lineReference = $comment->getLineReference();
        $files         = $this->diffService->getDiffFiles($review->getRevisions()->toArray());

        // find selected file
        $selectedFile = $this->diffFinder->findFileByPath($files, $lineReference->filePath);
        $lineRange    = [];
        if ($selectedFile !== null) {
            $lineRange = $this->diffFinder->findLinesAround($selectedFile, Type::notNull($lineReference), 3) ?? [];
        }

        $viewModel = new NewCommentViewModel($review, $comment, $selectedFile, $lineRange['before'] ?? [], $lineRange['after'] ?? []);

        return ['commentModel' => $viewModel];
    }
}
