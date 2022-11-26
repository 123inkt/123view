<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig;

use DR\GitCommitNotification\Entity\Config\ExternalLink;
use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Utility\Assert;
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
    /** @var ExternalLink[]|null */
    private ?array $externalLinks = null;

    public function __construct(private readonly ExternalLinkRepository $linkRepository)
    {
    }

    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('external_links', [$this, 'injectExternalLinks'], ['is_safe' => ['all']]),
        ];
    }

    public function injectExternalLinks(string $html): string
    {
        foreach ($this->getLinks() as $link) {
            $key = (string)$link->getPattern();
            $url = (string)$link->getUrl();

            $search = '/' . str_replace('\{\}', '([\w.-]+)', preg_quote($key, '/')) . '/';
            $html   = Assert::notNull(
                preg_replace_callback(
                    $search,
                    static function (array $matches) use ($url) {
                        $url = htmlspecialchars(str_replace('{}', urlencode($matches[1]), $url), ENT_QUOTES);

                        return sprintf('<a href="%s" class="external-link" target="_blank">%s</a>', $url, $matches[0]);
                    },
                    $html
                )
            );
        }

        return $html;
    }

    /**
     * @return ExternalLink[]
     */
    private function getLinks(): array
    {
        return $this->externalLinks ??= $this->linkRepository->findAll();
    }
}
