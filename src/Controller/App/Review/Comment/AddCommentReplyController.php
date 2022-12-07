<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Form\Review\AddCommentReplyFormType;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AddCommentReplyController extends AbstractController
{
    public function __construct(private readonly CommentReplyRepository $replyRepository, private readonly MessageBusInterface $bus)
    {
    }

    #[Route('app/comments/{id<\d+>}/add-reply', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] ?Comment $comment): Response
    {
        if ($comment === null) {
            $this->addFlash('warning', 'comment.was.deleted.meanwhile');

            return $this->refererRedirect(ProjectsController::class, filter: ['action']);
        }

        $form = $this->createForm(AddCommentReplyFormType::class, null, ['comment' => $comment]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return $this->refererRedirect(ReviewController::class, ['review' => $comment->getReview()]);
        }

        /** @var array{message: string} $data */
        $data = $form->getData();

        $user  = $this->getUser();
        $reply = new CommentReply();
        $reply->setUser($user);
        $reply->setComment($comment);
        $reply->setMessage($data['message']);
        $reply->setCreateTimestamp(time());
        $reply->setUpdateTimestamp(time());

        $this->replyRepository->save($reply, true);

        $this->bus->dispatch(
            new CommentReplyAdded((int)$comment->getReview()?->getId(), (int)$reply->getId(), (int)$user->getId(), $data['message'])
        );

        return $this->refererRedirect(ReviewController::class, ['review' => $comment->getReview()], ['action'], 'focus:reply:' . $reply->getId());
    }
}
