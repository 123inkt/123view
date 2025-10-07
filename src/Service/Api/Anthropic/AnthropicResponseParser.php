<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Anthropic;

use Anthropic\Client;
use DR\Review\Model\Api\Anthropic\CodeReviewResponse;
use DR\Review\Model\Api\Anthropic\PromptResponse;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class AnthropicResponseParser
{
    /**
     * @return CodeReviewResponse[]
     */
    public function parse(string $message): array
    {
        $result = [];
        $comments = explode("\n---\n", $message);

        foreach ($comments as $comment) {
            $comment = trim($comment);

            // match `## FILE: path/to/file.php:linenumber`
            if (preg_match('/^## FILE: (.+):(\d+)/m', $comment, $matches) !== 1) {
                continue;
            }
            $filepath   = $matches[1];
            $lineNumber = (int)$matches[2];

            // match `## COMMENT: till the end of the string
            if (preg_match('/^## COMMENT:\s*(.+)$/ms', $comment, $matches) !== 1) {
                continue;
            }
            $message = trim($matches[1]);
            $message = str_replace(":**", "**", $message);

            $result[] = new CodeReviewResponse($filepath, $lineNumber, $message);
        }

        return $result;
    }
}
