<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Utility\Assert;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteCommentController extends AbstractController
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly CommentEventMessageFactory $messageFactory,
        private readonly MessageBusInterface $bus
    ) {
    }

    #[Route('app/comments/{id<\d+>}', name: self::class, methods: 'DELETE')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(#[MapEntity] ?Comment $comment): Response
    {
        if ($comment === null) {
            return $this->refererRedirect(ProjectsController::class, filter: ['action']);
        }

        $this->denyAccessUnlessGranted(CommentVoter::DELETE, $comment);

        $this->commentRepository->remove($comment, true);
        $this->bus->dispatch($this->messageFactory->createRemoved($comment, $this->getUser()));

        $anchor = 'focus:line:' . Assert::notNull($comment->getLineReference())->lineAfter;

        return $this->refererRedirect(ReviewController::class, ['review' => $comment->getReview()], ['action'], $anchor);
    }
}
