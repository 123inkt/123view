<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use FD\CommonMarkEmoji\EmojiDataProvider;
use FD\CommonMarkEmoji\EmojiExtension;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use League\CommonMark\Extension\GithubFlavoredMarkdownExtension;
use League\CommonMark\MarkdownConverter;
use Psr\EventDispatcher\EventDispatcherInterface;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;

class CommonMarkdownConverter extends MarkdownConverter
{
    private const LANGUAGES = [
        "css",
        "ini",
        "javascript",
        "json",
        "apache",
        "less",
        "markdown",
        "php",
        "python",
        "scss",
        "bash",
        "sql",
        "typescript",
        "twig",
        "xml",
        "yaml",
    ];

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
        $environment->addRenderer(FencedCode::class, new FencedCodeRenderer(self::LANGUAGES));

        parent::__construct($environment);
    }
}
