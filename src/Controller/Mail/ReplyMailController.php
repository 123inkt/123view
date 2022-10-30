<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\Mail;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\CommentReply;
use DR\GitCommitNotification\ViewModel\Mail\CommentViewModel;
use DR\GitCommitNotification\ViewModelProvider\Mail\MailCommentViewModelProvider;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class ReplyMailController extends AbstractController
{
    public function __construct(private readonly MailCommentViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array<string, CommentViewModel>
     * @throws Throwable
     */
    #[Route('app/mail/reply/{id<\d+>}', name: self::class, methods: 'GET', condition: "env('APP_ENV') === 'dev'")]
    #[Template('mail/mail.comment.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('reply')]
    public function __invoke(CommentReply $reply): array
    {
        return ['commentModel' => $this->viewModelProvider->createReplyCommentViewModel($reply)];
    }
}
