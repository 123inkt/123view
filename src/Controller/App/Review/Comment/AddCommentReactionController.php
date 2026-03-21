<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AddCommentReactionController extends AbstractController
{
    public function __construct(private readonly CommentReplyRepository $replyRepository, private readonly MessageBusInterface $bus)
    {
    }

    #[Route('app/comments/{id<\d+>}/add-reaction', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] Comment $comment): JsonResponse
    {
        $message = $request->getContent();

        $user  = $this->getUser();
        $reply = new CommentReply();
        $reply->setUser($user);
        $reply->setComment($comment);
        $reply->setMessage($message);
        $reply->setCreateTimestamp(time());
        $reply->setUpdateTimestamp(time());

        $this->replyRepository->save($reply, true);

        $this->bus->dispatch(
            new CommentReplyAdded($comment->getReview()->getId(), (int)$reply->getId(), $user->getId(), $message, $comment->getFilePath())
        );

        return $this->json(['success' => true]);
    }
}
