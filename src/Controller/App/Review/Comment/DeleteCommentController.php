<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
    public function __invoke(#[MapEntity] ?Comment $comment): JsonResponse
    {
        if ($comment === null) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted(CommentVoter::DELETE, $comment);

        $messages = [$this->messageFactory->createRemoved($comment, $this->getUser())];
        foreach ($comment->getReplies() as $reply) {
            $messages[] = $this->messageFactory->createReplyRemove($reply, $this->getUser());
        }

        $this->commentRepository->remove($comment, true);

        foreach ($messages as $message) {
            $this->bus->dispatch($message);
        }

        return $this->json(['success' => true]);
    }
}
