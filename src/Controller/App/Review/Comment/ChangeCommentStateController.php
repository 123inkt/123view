<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Request\Comment\ChangeCommentStateRequest;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class ChangeCommentStateController extends AbstractController
{
    public function __construct(private readonly CommentRepository $commentRepository, private readonly TranslatorInterface $translator)
    {
    }

    #[Route('app/comments/{id<\d+>}/state', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(ChangeCommentStateRequest $request, #[MapEntity] ?Comment $comment): JsonResponse
    {
        if ($comment === null) {
            return $this->json(['success' => false, 'error' => $this->translator->trans('comment.was.deleted.meanwhile')], Response::HTTP_NOT_FOUND);
        }

        $comment->setState($request->getState());
        $this->commentRepository->save($comment, true);

        return $this->json(['success' => true, 'commentId' => $comment->getId()]);
    }
}
