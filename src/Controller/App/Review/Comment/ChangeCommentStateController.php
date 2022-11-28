<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Message\Comment\CommentResolved;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Request\Comment\ChangeCommentStateRequest;
use DR\GitCommitNotification\Security\Role\Roles;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ChangeCommentStateController extends AbstractController
{
    public function __construct(private readonly CommentRepository $commentRepository, private readonly MessageBusInterface $bus)
    {
    }

    #[Route('app/comments/{id<\d+>}/state', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('comment')]
    public function __invoke(ChangeCommentStateRequest $request, Comment $comment): RedirectResponse
    {
        $currentState = $comment->getState();
        $state        = $request->getState();

        $comment->setState($state);
        $this->commentRepository->save($comment, true);

        if ($currentState !== $state && $state === CommentStateType::RESOLVED) {
            $this->bus->dispatch(new CommentResolved((int)$comment->getReview()?->getId(), (int)$comment->getId(), (int)$this->getUser()->getId()));
        }

        return $this->refererRedirect(ReviewController::class, ['review' => $comment->getReview()]);
    }
}
