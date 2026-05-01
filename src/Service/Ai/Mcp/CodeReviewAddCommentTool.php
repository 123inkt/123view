<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Mcp;

use DR\Review\Entity\User\User;
use DR\Review\Service\Ai\AddCommentService;
use DR\Utils\Assert;
use Mcp\Capability\Attribute\McpTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Symfony\Bundle\SecurityBundle\Security;
use Throwable;

#[McpTool('add_comment', 'Add a comment to a code review at a specific file and line number. Optionally include a code suggestion.')]
readonly class CodeReviewAddCommentTool
{
    public function __construct(private Security $security, private AddCommentService $commentService)
    {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(
        #[Schema(description: 'The review id of the code review', minimum: 1)] int $codeReviewId,
        #[Schema(description: 'The filepath of the file to comment on relative to the git repository root')] string $filepath,
        #[Schema(description: 'The line number in the file to comment on', minimum: 1)] int $lineNumber,
        #[Schema(description: 'The comment text to add, must be valid markdown')] string $message,
        #[Schema(description: 'The code suggestion to include in the comment, must be valid markdown')] ?string $codeSuggestion
    ): string {
        $this->commentService->addComment(
            Assert::isInstanceOf($this->security->getUser(), User::class),
            $codeReviewId,
            $filepath,
            $lineNumber,
            $message,
            $codeSuggestion
        );

        return 'Comment added successfully.';
    }
}
