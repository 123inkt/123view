<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Doctrine\Type\CommentStateType;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;

class ChangeCommentStateController extends AbstractController
{
    public function __construct(private readonly CommentRepository $commentRepository)
    {
    }

    #[Route('app/comments/{id<\d+>}/state', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('comment')]
    public function __invoke(Request $request, Comment $comment): RedirectResponse
    {
        $state = $request->request->get('state');
        if (in_array($state, CommentStateType::VALUES, true) === false) {
            throw new BadRequestHttpException('Invalid state value: ' . $state);
        }

        $comment->setState($state);
        $this->commentRepository->save($comment, true);

        return $this->refererRedirect(ReviewController::class, ['id' => $comment->getReview()?->getId()]);
    }
}
