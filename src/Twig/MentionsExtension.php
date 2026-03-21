<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use Twig\Attribute\AsTwigFilter;

class MentionsExtension
{
    public function __construct(private readonly CommentMentionService $mentionService)
    {
    }

    #[AsTwigFilter(name: 'mentions')]
    public function convert(string $string): string
    {
        return $this->mentionService->replaceMentionedUsers($string, $this->mentionService->getMentionedUsers($string));
    }
}
