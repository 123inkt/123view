<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Mcp;

use DR\Review\Entity\User\User;
use DR\Review\Exception\Ai\CommentReplyNotFoundException;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Utils\Assert;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

#[McpTool('delete_comment_reply', 'Delete a comment reply. Authorization: only allowed to deleted own replies')]
readonly class DeleteCommentReplyTool
{
    public function __construct(
        private CommentReplyRepository $replyRepository,
        private CommentEventMessageFactory $messageFactory,
        private MessageBusInterface $bus,
        private Security $security
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(#[Schema(description: 'The id of the comment reply to deleted', minimum: 1)] int $replyId): string
    {
        $reply = $this->replyRepository->find($replyId);
        if ($reply === null) {
            throw new CommentReplyNotFoundException($replyId);
        }
        if ($this->security->isGranted(CommentReplyVoter::DELETE, $reply) === false) {
            throw new AccessDeniedHttpException();
        }

        $event = $this->messageFactory->createReplyRemoved($reply, Assert::isInstanceOf($this->security->getUser(), User::class));
        $this->replyRepository->remove($reply, true);
        $this->bus->dispatch($event);

        return 'Comment reply deleted';
    }
}
