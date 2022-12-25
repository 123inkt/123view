<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Request\Comment\ChangeCommentStateRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChangeCommentStateController extends AbstractController
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly CommentEventMessageFactory $messageFactory,
        private readonly MessageBusInterface $bus
    ) {
    }

    #[Route('app/comments/{id<\d+>}/state', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(ChangeCommentStateRequest $request, #[MapEntity] ?Comment $comment): RedirectResponse
    {
        if ($comment === null) {
            $this->addFlash('warning', 'comment.was.deleted.meanwhile');

            return $this->refererRedirect(ProjectsController::class, filter: ['action']);
        }

        $currentState = $comment->getState();
        $state        = $request->getState();

        $comment->setState($state);
        $this->commentRepository->save($comment, true);

        if ($currentState !== $state && $state === CommentStateType::RESOLVED) {
            $this->bus->dispatch($this->messageFactory->createResolved($comment, $this->getUser()));
        }

        return $this->refererRedirect(ReviewController::class, ['review' => $comment->getReview()], [], 'focus:comment:' . $comment->getId());
    }
}
