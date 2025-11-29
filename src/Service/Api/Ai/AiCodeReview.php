<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Ai;

use DR\Review\Entity\Review\Comment;
use Symfony\AI\Platform\Contract\JsonSchema\Attribute\With;

class AiCodeReview
{
    /** @var Comment[] the comments on the code review */
    #[With(minItems: 0, maxItems: 10)]
    public array $comments = [];
}
