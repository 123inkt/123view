<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Form\Review\AddCommentReplyFormType;
use DR\GitCommitNotification\Message\Comment\CommentReplyAdded;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class AddCommentReplyController extends AbstractController
{
    public function __construct(private readonly CommentReplyRepository $replyRepository, private readonly MessageBusInterface $bus)
    {
    }

    #[Route('app/comments/{id<\d+>}/add-reply', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('comment')]
    public function __invoke(Request $request, Comment $comment): Response
    {
        $form = $this->createForm(AddCommentReplyFormType::class, null, ['comment' => $comment]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return $this->refererRedirect(ReviewController::class, ['review' => $comment->getReview()]);
        }

        /** @var array{message: string} $data */
        $data = $form->getData();

        $reply = new CommentReply();
        $reply->setUser($this->getUser());
        $reply->setComment($comment);
        $reply->setMessage($data['message']);
        $reply->setCreateTimestamp(time());
        $reply->setUpdateTimestamp(time());

        $this->replyRepository->save($reply, true);

        $this->bus->dispatch(new CommentReplyAdded((int)$comment->getReview()?->getId(), (int)$reply->getId()));

        return $this->refererRedirect(ReviewController::class, ['review' => $comment->getReview()], ['action'], 'focus:reply:' . $reply->getId());
    }
}
