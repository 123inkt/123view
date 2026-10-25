<?php
declare(strict_types=1);

namespace DR\Review\Service\Ai\Tool;

use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Ai\AddCommentService;
use DR\Utils\Assert;
use Symfony\AI\Agent\Toolbox\Attribute\AsTool;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\Schema;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Throwable;

#[AsTool('add_comment', 'Add a comment to a code review at a specific file and line number. Optionally include a code suggestion.')]
readonly class CodeReviewAddCommentTool
{
    public function __construct(
        #[Autowire(env: 'AI_COMMENT_USER_ID')] private ?int $userId,
        private UserRepository $userRepository,
        private AddCommentService $commentService,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function __invoke(
        #[Schema(description: 'The CODE_REVIEW_ID of the code review', minimum: 1)] int $codeReviewId,
        #[Schema(description: 'The filepath of the file to comment on relative to the git repository root')] string $filepath,
        #[Schema(description: 'The line number in the file to comment on', minimum: 1)] int $lineNumber,
        #[Schema(description: 'The comment text to add, must be valid markdown')] string $message,
        #[Schema(description: 'The code suggestion to include in the comment, must be valid markdown')] ?string $codeSuggestion
    ): string {
        $user = Assert::notNull($this->userRepository->find(Assert::notNull($this->userId)));

        $this->commentService->addComment(
            $user,
            $codeReviewId,
            $filepath,
            $lineNumber,
            $message,
            $codeSuggestion
        );

        return 'Comment added successfully.';
    }
}
