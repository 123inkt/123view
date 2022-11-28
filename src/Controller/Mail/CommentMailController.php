<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\Mail;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\Utility\Assert;
use DR\GitCommitNotification\ViewModel\Mail\CommentViewModel;
use DR\GitCommitNotification\ViewModelProvider\Mail\MailCommentViewModelProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class CommentMailController extends AbstractController
{
    public function __construct(private readonly MailCommentViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array<string, CommentViewModel>
     * @throws Throwable
     */
    #[Route('app/mail/comment/{id<\d+>}', name: self::class, methods: 'GET', condition: "env('APP_ENV') === 'dev'")]
    #[Template('mail/mail.comment.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Entity('comment')]
    public function __invoke(Comment $comment): array
    {
        $review = Assert::notNull($comment->getReview());

        return ['commentModel' => $this->viewModelProvider->createCommentViewModel($review, $comment)];
    }
}
