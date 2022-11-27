<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HtmlMailExtension extends AbstractExtension
{
    /**
     * @return TwigFilter[]
     */
    public function getFilters(): array
    {
        return [new TwigFilter('html_mail', [$this, 'convert'], ['is_safe' => ['all']])];
    }

    /**
     * Email-client html presentation is limited and disallows to remove margin from ul and ol. Transform this element to simple plaintext
     * bullet list
     */
    public function convert(string $string): string
    {
        // remove `ul` and `ol`
        $string = (string)preg_replace('#</?[ou]l.*?>#', '', $string);

        // replace `<li>` with `● `
        $string = (string)preg_replace('#<li.*?>#', '&#9679; ', $string);

        // replace `</li>` with `<br>`
        return (string)preg_replace('#</li.*?>#', '<br>', $string);
    }
}
