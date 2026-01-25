<?php
declare(strict_types=1);

namespace DR\Review\Controller\Mail;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\Mail\CommentViewModel;
use DR\Review\ViewModelProvider\Mail\MailCommentViewModelProvider;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class CommentResolvedMailController extends AbstractController
{
    public function __construct(private readonly MailCommentViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array<string, CommentViewModel>
     * @throws Throwable
     */
    #[Route('app/mail/comment-resolved/{id<\d+>}', name: self::class, methods: 'GET', condition: "env('APP_ENV') === 'dev'")]
    #[Template('mail/mail.comment.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(#[MapEntity] Comment $comment): array
    {
        /** @var CodeReview $review */
        $review = $comment->getReview();

        return ['commentModel' => $this->viewModelProvider->createCommentViewModel($review, $comment, null, $this->getUser())];
    }
}
