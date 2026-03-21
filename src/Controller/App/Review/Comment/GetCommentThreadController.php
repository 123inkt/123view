<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Model\Review\Action\AddCommentReplyAction;
use DR\Review\Model\Review\Action\EditCommentAction;
use DR\Review\Model\Review\Action\EditCommentReplyAction;
use DR\Review\Request\Comment\GetCommentThreadRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModelProvider\CommentViewModelProvider;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GetCommentThreadController extends AbstractController
{
    public function __construct(private readonly CommentViewModelProvider $commentModelProvider)
    {
    }

    /**
     * @return array<string, bool|object|null>
     */
    #[Route('app/comments/{id<\d+>}', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Template('app/review/comment/comment.html.twig')]
    public function __invoke(GetCommentThreadRequest $request, #[MapEntity] Comment $comment): array
    {
        $data = ['comment' => $comment, 'visible' => true, 'review' => $comment->getReview()];

        $action = $request->getAction();
        if ($action instanceof EditCommentAction) {
            $data['editCommentForm'] = $this->commentModelProvider->getEditCommentViewModel($action);
        } elseif ($action instanceof AddCommentReplyAction) {
            $data['replyCommentForm'] = $this->commentModelProvider->getReplyCommentViewModel($action);
        } elseif ($action instanceof EditCommentReplyAction) {
            $data['editReplyCommentForm'] = $this->commentModelProvider->getEditCommentReplyViewModel($action);
        }

        return $data;
    }
}
