<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Mcp;

use DR\Review\Entity\User\User;
use DR\Review\Exception\Ai\CommentNotFoundException;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Utils\Assert;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

#[McpTool(
    'delete_comment',
    'Delete a comment. Review id must match the id of the review of the comment id. ' .
    'Authorization: only allowed to deleted own comments')]
readonly class DeleteCommentTool
{
    public function __construct(
        private CommentRepository $commentRepository,
        private CommentEventMessageFactory $messageFactory,
        private MessageBusInterface $bus,
        private Security $security
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(#[Schema(description: 'The id of the comment to update', minimum: 1)] int $commentId): string
    {
        $comment = $this->commentRepository->find($commentId);
        if ($comment === null) {
            throw new CommentNotFoundException($commentId);
        }
        if ($this->security->isGranted(CommentVoter::DELETE, $comment) === false) {
            throw new AccessDeniedHttpException();
        }

        $messages = [];
        foreach ($comment->getReplies() as $reply) {
            $messages[] = $this->messageFactory->createReplyRemoved($reply, Assert::isInstanceOf($this->security->getUser(), User::class));
        }

        $this->commentRepository->remove($comment, true);

        foreach ($messages as $message) {
            $this->bus->dispatch($message);
        }

        return 'Comment deleted successfully';
    }
}
