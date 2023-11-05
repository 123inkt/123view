<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class IdeButtonExtension extends AbstractExtension
{
    public function __construct(
        private readonly bool $enabled,
        private readonly string $url,
        private readonly string $title,
        private readonly Environment $twig,
    ) {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [new TwigFunction('ide_button', [$this, 'createLink'], ['is_safe' => ['all']])];
    }

    /**
     * @throws SyntaxError|RuntimeError|LoaderError
     */
    public function createLink(string $file, int $line = 1): string
    {
        if ($this->enabled === false) {
            return '';
        }

        return $this->twig->render(
            '/extension/ide-button.widget.html.twig',
            [
                'url' => str_ireplace(['{file}', '{line}'], [urlencode($file), $line], $this->url),
                'title' => $this->title
            ]
        );
    }
}
