<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\Form\Review\EditCommentReplyFormType;
use DR\GitCommitNotification\Message\Comment\CommentReplyUpdated;
use DR\GitCommitNotification\Repository\Review\CommentReplyRepository;
use DR\GitCommitNotification\Security\Voter\CommentReplyVoter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class UpdateCommentReplyController extends AbstractController
{
    public function __construct(private readonly CommentReplyRepository $replyRepository, private readonly MessageBusInterface $bus)
    {
    }

    #[Route('app/comment-replies/{id<\d+>}', name: self::class, methods: 'POST')]
    #[IsGranted('ROLE_USER')]
    #[Entity('reply')]
    public function __invoke(Request $request, CommentReply $reply): Response
    {
        $originalComment = (string)$reply->getMessage();
        $this->denyAccessUnlessGranted(CommentReplyVoter::EDIT, $reply);

        $form = $this->createForm(EditCommentReplyFormType::class, $reply, ['reply' => $reply]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return $this->refererRedirect(ReviewController::class, ['review' => $reply->getComment()?->getReview()]);
        }

        $reply->setUpdateTimestamp(time());
        $this->replyRepository->save($reply, true);

        if ($reply->getMessage() !== $originalComment) {
            $this->bus->dispatch(new CommentReplyUpdated((int)$reply->getComment()?->getReview()?->getId(), (int)$reply->getId(), $originalComment));
        }

        return $this->refererRedirect(
            ReviewController::class,
            ['review' => $reply->getComment()?->getReview()],
            ['action'],
            'focus:reply:' . $reply->getId()
        );
    }
}
