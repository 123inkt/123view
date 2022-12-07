<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Form\Review\EditCommentFormType;
use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\Voter\CommentVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UpdateCommentController extends AbstractController
{
    public function __construct(private readonly CommentRepository $commentRepository, private readonly MessageBusInterface $bus)
    {
    }

    #[Route('app/comments/{id<\d+>}', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] ?Comment $comment): Response
    {
        if ($comment === null) {
            $this->addFlash('warning', 'comment.was.deleted.meanwhile');

            return $this->refererRedirect(ProjectsController::class, filter: ['action']);
        }

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
            $this->bus->dispatch(
                new CommentUpdated((int)$comment->getReview()?->getId(), (int)$comment->getId(), (int)$this->getUser()->getId(), $originalComment)
            );
        }

        return $this->refererRedirect(ReviewController::class, ['review' => $comment->getReview()], ['action'], 'focus:comment:' . $comment->getId());
    }
}
