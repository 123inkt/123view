<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Request\Comment\ChangeCommentStateRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangeCommentStateController extends AbstractController
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly CommentEventMessageFactory $messageFactory,
        private readonly TranslatorInterface $translator,
        private readonly MessageBusInterface $bus
    ) {
    }

    #[Route('app/comments/{id<\d+>}/state', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(ChangeCommentStateRequest $request, #[MapEntity] ?Comment $comment): JsonResponse
    {
        if ($comment === null) {
            return $this->json(['success' => false, 'error' => $this->translator->trans('comment.was.deleted.meanwhile')], Response::HTTP_NOT_FOUND);
        }

        $currentState = $comment->getState();
        $state        = $request->getState();

        $comment->setState($state);
        $this->commentRepository->save($comment, true);

        if ($currentState !== $state && $state === CommentStateType::RESOLVED) {
            $this->bus->dispatch($this->messageFactory->createResolved($comment, $this->getUser()));
        }

        return $this->json(['success' => true, 'commentId' => $comment->getId()]);
    }
}
