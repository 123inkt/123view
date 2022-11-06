<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\LineReference;
use DR\GitCommitNotification\Form\Review\AddCommentFormType;
use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class AddCommentController extends AbstractController
{
    public function __construct(private readonly CommentRepository $commentRepository, private readonly MessageBusInterface $bus)
    {
    }

    #[Route('app/reviews/{id<\d+>}/add-comment', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): Response
    {
        $form = $this->createForm(AddCommentFormType::class, null, ['review' => $review]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return $this->refererRedirect(ReviewController::class, ['id' => $review->getId()]);
        }

        /** @var array{lineReference: string, message: string} $data */
        $data = $form->getData();

        $lineReference = LineReference::fromString($data['lineReference']);

        $comment = new Comment();
        $comment->setUser($this->getUser());
        $comment->setReview($review);
        $comment->setFilePath($lineReference->filePath);
        $comment->setLineReference($lineReference);
        $comment->setMessage($data['message']);
        $comment->setCreateTimestamp(time());
        $comment->setUpdateTimestamp(time());

        $this->commentRepository->save($comment, true);

        $this->bus->dispatch(new CommentAdded((int)$comment->getReview()?->getId(), (int)$comment->getId()));

        return $this->refererRedirect(ReviewController::class, ['id' => $review->getId()], ['action']);
    }
}
