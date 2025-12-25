<?php
declare(strict_types=1);

namespace DR\Review\Model\Ai;

use Symfony\AI\Platform\Contract\JsonSchema\Attribute\With;

class AiCodeReviewComment
{
    /** @var string the file path of the file the comment is related to as specified by the diff */
    public string $file;

    /** @var int the line number in the file the comment is related to */
    public int $lineNumber;

    /** @var string the comment text in markdown format, should not contain code examples */
    #[With(minLength: 1, maxLength: 500)]
    public string $comment;

    /** @var string|null an optional code suggestion in markdown format*/
    public ?string $suggestion = null;
}
