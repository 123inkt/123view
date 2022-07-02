<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig;

use DR\GitCommitNotification\Entity\Config\ExternalLink;
use LogicException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

/**
 * Extension to inject urls to external parties by pattern.
 * Pattern:
 *   'JB{}' => 'https://mycompany.com/jira/{}'
 * Replaces:
 *   JB1234 with <a href="https://mycompany.com/jira/JB1234">JB1234</a>
 */
class ExternalLinkExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('external_links', [$this, 'injectExternalLinks'], ['is_safe' => ['all']]),
        ];
    }

    /**
     * @param ExternalLink[] $links
     */
    public function injectExternalLinks(string $html, array $links): string
    {
        foreach ($links as $link) {
            $key = (string)$link->getPattern();
            $url = (string)$link->getUrl();

            $search = '/' . str_replace('\{\}', '([\w.-]+)', preg_quote($key, '/')) . '/';
            $result = preg_replace_callback(
                $search,
                static function (array $matches) use ($url) {
                    $url = htmlspecialchars(str_replace('{}', urlencode($matches[1]), $url), ENT_QUOTES);

                    return sprintf('<a href="%s" class="external-link">%s</a>', $url, $matches[0]);
                },
                $html
            );

            // @codeCoverageIgnoreStart
            if ($result === null) {
                throw new LogicException('Failed to replace external links with regex: ' . $search);
            }
            // @codeCoverageIgnoreEnd
            $html = $result;
        }

        return $html;
    }
}
