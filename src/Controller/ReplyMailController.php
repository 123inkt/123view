<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Service\CodeReview\DiffFinder;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService;
use DR\GitCommitNotification\Utility\Type;
use DR\GitCommitNotification\ViewModel\Mail\ReplyCommentViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class ReplyMailController extends AbstractController
{
    public function __construct(
        private readonly ReviewDiffService $diffService,
        private readonly DiffFinder $diffFinder
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('app/mail/reply/{id<\d+>}', name: self::class, methods: 'GET')]
    #[Template('mail/mail.comment.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('reply')]
    public function __invoke(CommentReply $reply): array
    {
        /** @var Comment $comment */
        $comment = $reply->getComment();
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

        $replies = [];
        foreach ($comment->getReplies() as $reaction) {
            $replies[] = $reaction;
            if ($reaction === $reply) {
                break;
            }
        }

        $viewModel = new ReplyCommentViewModel($review, $comment, $replies, $selectedFile, $lineRange['before'] ?? [], $lineRange['after'] ?? []);

        return ['commentModel' => $viewModel];
    }
}
