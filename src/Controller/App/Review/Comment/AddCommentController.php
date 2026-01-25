<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Form\Review\AddCommentFormType;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AddCommentController extends AbstractController
{
    public function __construct(private readonly CommentRepository $commentRepository)
    {
    }

    #[Route('app/reviews/{id<\d+>}/add-comment', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] CodeReview $review): JsonResponse
    {
        $user    = $this->getUser();
        $comment = new Comment();
        $comment->setUser($user);
        $comment->setMessage('');
        $comment->setTag(null);
        $comment->setReview($review);
        $comment->setCreateTimestamp(time());
        $comment->setUpdateTimestamp(time());

        $form = $this->createForm(AddCommentFormType::class, $comment, ['review' => $review]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return $this->json(['success' => false], Response::HTTP_BAD_REQUEST);
        }

        $this->commentRepository->save($comment, true);

        $url = $this->generateUrl(GetCommentThreadController::class, ['id' => (int)$comment->getId()]);

        return $this->json(['success' => true, 'commentUrl' => $url]);
    }
}
