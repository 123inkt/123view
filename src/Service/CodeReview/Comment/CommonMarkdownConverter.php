<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use FD\CommonMarkEmoji\EmojiDataProvider;
use FD\CommonMarkEmoji\EmojiExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Psr\EventDispatcher\EventDispatcherInterface;
use Tempest\Highlight\CommonMark\HighlightExtension;

class CommonMarkdownConverter extends MarkdownConverter
{
    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $environment = new Environment(
            [
                'html_input'         => 'escape',
                'allow_unsafe_links' => false,
                'renderer'           => [
                    'block_separator' => "<br>\n",
                    'inner_separator' => "<br>\n",
                    'soft_break'      => "<br>\n"
                ]
            ]
        );
        $environment->setEventDispatcher($eventDispatcher);
        $environment->addExtension(new CommonMarkCoreExtension());
        $environment->addExtension(new GithubFlavoredMarkdownExtension());
        $environment->addExtension(new EmojiExtension(EmojiDataProvider::full()));
        $environment->addExtension(new HighlightExtension());

        parent::__construct($environment);
    }
}
