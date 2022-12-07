<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Form\Review\EditCommentReplyFormType;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\Voter\CommentReplyVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UpdateCommentReplyController extends AbstractController
{
    public function __construct(private readonly CommentReplyRepository $replyRepository, private readonly MessageBusInterface $bus)
    {
    }

    #[Route('app/comment-replies/{id<\d+>}', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] ?CommentReply $reply): Response
    {
        if ($reply === null) {
            $this->addFlash('warning', 'comment.was.deleted.meanwhile');

            return $this->refererRedirect(ProjectsController::class, filter: ['action']);
        }

        $originalComment = (string)$reply->getMessage();
        $this->denyAccessUnlessGranted(CommentReplyVoter::EDIT, $reply);

        $form = $this->createForm(EditCommentReplyFormType::class, $reply, ['reply' => $reply]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return $this->refererRedirect(ReviewController::class, ['review' => $reply->getComment()?->getReview()]);
        }

        $reply->setUpdateTimestamp(time());
        $this->replyRepository->save($reply, true);

        if ($reply->getMessage() !== $originalComment) {
            $this->bus->dispatch(
                new CommentReplyUpdated(
                    (int)$reply->getComment()?->getReview()?->getId(),
                    (int)$reply->getId(),
                    (int)$this->getUser()->getId(),
                    $originalComment
                )
            );
        }

        return $this->refererRedirect(
            ReviewController::class,
            ['review' => $reply->getComment()?->getReview()],
            ['action'],
            'focus:reply:' . $reply->getId()
        );
    }
}
