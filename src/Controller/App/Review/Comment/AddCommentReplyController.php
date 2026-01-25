<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Form\Review\AddCommentReplyFormType;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class AddCommentReplyController extends AbstractController
{
    public function __construct(
        private readonly CommentReplyRepository $replyRepository,
        private readonly TranslatorInterface $translator,
        private readonly MessageBusInterface $bus
    ) {
    }

    #[Route('app/comments/{id<\d+>}/add-reply', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] ?Comment $comment): JsonResponse
    {
        if ($comment === null) {
            return $this->json(['success' => false, 'error' => $this->translator->trans('comment.was.deleted.meanwhile')], Response::HTTP_NOT_FOUND);
        }

        $user = $this->getUser();
        $reply = new CommentReply();
        $reply->setUser($user);
        $reply->setMessage('');
        $reply->setTag(null);
        $reply->setComment($comment);
        $reply->setCreateTimestamp(time());
        $reply->setUpdateTimestamp(time());

        $form = $this->createForm(AddCommentReplyFormType::class, $reply, ['comment' => $comment]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return $this->json(['success' => false], Response::HTTP_BAD_REQUEST);
        }

        $this->replyRepository->save($reply, true);

        $this->bus->dispatch(
            new CommentReplyAdded(
                $comment->getReview()->getId(),
                (int)$reply->getId(),
                $user->getId(),
                $reply->getMessage(),
                $comment->getFilePath()
            )
        );

        return $this->json(['success' => true, 'commentId' => $comment->getId()]);
    }
}
