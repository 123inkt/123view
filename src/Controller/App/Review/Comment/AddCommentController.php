<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Form\Review\AddCommentFormType;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AddCommentController extends AbstractController
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly CommentEventMessageFactory $messageFactory,
        private readonly MessageBusInterface $bus
    ) {
    }

    #[Route('app/reviews/{id<\d+>}/add-comment', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] CodeReview $review): JsonResponse
    {
        $form = $this->createForm(AddCommentFormType::class, null, ['review' => $review]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return $this->json(['success' => false], Response::HTTP_BAD_REQUEST);
        }

        /** @var array{lineReference: string, message: string} $data */
        $data = $form->getData();

        $lineReference = LineReference::fromString($data['lineReference']);

        $user    = $this->getUser();
        $comment = new Comment();
        $comment->setUser($user);
        $comment->setReview($review);
        $comment->setFilePath($lineReference->filePath);
        $comment->setLineReference($lineReference);
        $comment->setMessage($data['message']);
        $comment->setCreateTimestamp(time());
        $comment->setUpdateTimestamp(time());

        $this->commentRepository->save($comment, true);
        $this->bus->dispatch($this->messageFactory->createAdded($comment, $user));

        $url = $this->generateUrl(GetCommentThreadController::class, ['id' => (int)$comment->getId()]);

        return $this->json(['success' => true, 'commentUrl' => $url]);
    }
}
