<?php
declare(strict_types=1);

namespace DR\Review\Service\Mcp;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\User\User;
use DR\Review\Exception\Review\CommentNotFoundException;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Utils\Assert;
use Mcp\Capability\Attribute\McpTool;
use Mcp\Capability\Attribute\Schema;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

#[McpTool('add_comment_reply', 'Add a Markdown reply to a non-draft comment. Returns the new reply id. Does not reopen resolved comments.')]
readonly class AddCommentReplyTool
{
    public function __construct(
        private CommentRepository $commentRepository,
        private CommentReplyRepository $commentReplyRepository,
        private Security $security,
        private MessageBusInterface $bus
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(
        #[Schema(description: 'The id of the comment to reply to', minimum: 1)] int $commentId,
        #[Schema(description: 'The reply text in Markdown. Edges are trimmed; blank or overly long messages are rejected.')] string $message,
    ): int {
        $comment = $this->commentRepository->find($commentId);
        if ($comment === null) {
            throw new CommentNotFoundException($commentId);
        }
        if ($comment->getType() === CommentTypeEnum::Draft) {
            throw new BadRequestHttpException('Replies cannot be added to draft comments.');
        }

        $message = trim($message);
        if ($message === '') {
            throw new BadRequestHttpException('Reply message must not be blank.');
        }
        if (mb_strlen($message) > Comment::MAX_COMMENT_LENGTH) {
            throw new BadRequestHttpException('Reply message must not exceed ' . Comment::MAX_COMMENT_LENGTH . ' characters.');
        }

        $user  = Assert::isInstanceOf($this->security->getUser(), User::class);
        $reply = new CommentReply();
        $reply->setComment($comment);
        $reply->setUser($user);
        $reply->setMessage($message);
        $reply->setTag(null);

        $this->commentReplyRepository->save($reply, true);
        $this->bus->dispatch(new CommentReplyAdded(
            $comment->getReview()->getId(),
            $reply->getId(),
            $user->getId(),
            $reply->getMessage(),
            $comment->getFilePath()
        ));

        return $reply->getId();
    }
}
