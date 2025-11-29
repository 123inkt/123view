<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Ai;

use Symfony\AI\Platform\Contract\JsonSchema\Attribute\With;

class AiCodeReviewComment
{
    /** @var string the absolute path of the file the comment is related to */
    public string $file;

    /** @var int the line number in the file the comment is related to */
    #[With(minimum: 1)]
    public int $lineNumber;

    /** @var string the comment text in markdown, should not contain code examples */
    #[With(minLength: 1, maxLength: 500)]
    public string $comment;

    /** @var string|null an optional code suggestion in markdown format with language name specified when possible */
    public ?string $suggestion = null;
}
