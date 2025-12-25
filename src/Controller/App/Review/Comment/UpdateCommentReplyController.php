<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Form\Review\EditCommentReplyFormType;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\Voter\CommentReplyVoter;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class UpdateCommentReplyController extends AbstractController
{
    public function __construct(
        private readonly CommentReplyRepository $replyRepository,
        private readonly TranslatorInterface $translator,
        private readonly MessageBusInterface $bus
    ) {
    }

    #[Route('app/comment-replies/{id<\d+>}', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] ?CommentReply $reply): JsonResponse
    {
        if ($reply === null) {
            return $this->json(['success' => false, 'error' => $this->translator->trans('comment.was.deleted.meanwhile')], Response::HTTP_NOT_FOUND);
        }

        $originalComment = $reply->getMessage();
        $this->denyAccessUnlessGranted(CommentReplyVoter::EDIT, $reply);

        $form = $this->createForm(EditCommentReplyFormType::class, $reply, ['reply' => $reply]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return $this->json(['success' => false], Response::HTTP_BAD_REQUEST);
        }

        $reply->setUpdateTimestamp(time());
        $this->replyRepository->save($reply, true);

        if ($reply->getMessage() !== $originalComment) {
            $this->bus->dispatch(
                new CommentReplyUpdated(
                    $reply->getComment()->getReview()->getId(),
                    (int)$reply->getId(),
                    $this->getUser()->getId(),
                    $originalComment
                )
            );
        }

        return $this->json(['success' => true, 'commentId' => $reply->getComment()->getId()]);
    }
}
