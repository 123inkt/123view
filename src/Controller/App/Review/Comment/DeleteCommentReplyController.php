<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteCommentReplyController extends AbstractController
{
    public function __construct(
        private readonly CommentReplyRepository $replyRepository,
        private readonly CommentEventMessageFactory $messageFactory,
        private readonly MessageBusInterface $bus
    ) {
    }

    #[Route('app/comment-replies/{id<\d+>}', name: self::class, methods: 'DELETE')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(#[MapEntity] ?CommentReply $reply): JsonResponse
    {
        if ($reply === null) {
            throw new NotFoundHttpException();
        }

        $this->denyAccessUnlessGranted(CommentReplyVoter::DELETE, $reply);

        $event = $this->messageFactory->createReplyRemoved($reply, $this->getUser());
        $this->replyRepository->remove($reply, true);
        $this->bus->dispatch($event);

        return $this->json(['success' => true]);
    }
}
