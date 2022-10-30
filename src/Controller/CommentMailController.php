<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\ViewModel\Mail\NewCommentViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class CommentMailController extends AbstractController
{
    #[Route('app/mail/{id<\d+>}', name: self::class, methods: 'GET')]
    #[Template('mail/new.comment.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('comment')]
    public function __invoke(Comment $comment): array
    {
        $viewModel = new NewCommentViewModel($comment->getReview(), $comment);

        return ['newCommentModel' => $viewModel];
    }
}
