<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig;

use DR\GitCommitNotification\Service\CodeReview\Comment\CommentMentionService;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class MentionsExtension extends AbstractExtension
{
    public function __construct(private readonly CommentMentionService $mentionService)
    {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [new TwigFilter('mentions', [$this, 'convert'])];
    }

    public function convert(string $string): string
    {
        foreach ($this->mentionService->getMentionedUsers($string) as $match => $user) {
            $markdown = sprintf('[@%s](mailto:%s)', $user->getName(), $user->getEmail());
            $string   = str_replace($match, $markdown, $string);
        }

        return $string;
    }
}
