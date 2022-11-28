<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Form\Review\EditCommentFormType;
use DR\GitCommitNotification\Message\Comment\CommentUpdated;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\Security\Voter\CommentVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class UpdateCommentController extends AbstractController
{
    public function __construct(private readonly CommentRepository $commentRepository, private readonly MessageBusInterface $bus)
    {
    }

    #[Route('app/comments/{id<\d+>}', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('comment')]
    public function __invoke(Request $request, Comment $comment): Response
    {
        $originalComment = (string)$comment->getMessage();
        $this->denyAccessUnlessGranted(CommentVoter::EDIT, $comment);

        $form = $this->createForm(EditCommentFormType::class, $comment, ['comment' => $comment]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return $this->refererRedirect(ReviewController::class, ['review' => $comment->getReview()]);
        }

        $comment->setUpdateTimestamp(time());
        $this->commentRepository->save($comment, true);

        if ($comment->getMessage() !== $originalComment) {
            $this->bus->dispatch(new CommentUpdated((int)$comment->getReview()?->getId(), (int)$comment->getId(), $originalComment));
        }

        return $this->refererRedirect(ReviewController::class, ['review' => $comment->getReview()], ['action'], 'focus:comment:' . $comment->getId());
    }
}
