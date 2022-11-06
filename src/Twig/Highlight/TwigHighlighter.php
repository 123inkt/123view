<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig\Highlight;

class TwigHighlighter implements HighlighterInterface
{
    public const EXTENSION = 'twig';

    private const PATTERN = [
        "(a(nd|pply|utoescape))",
        "(block)",
        "(cache)",
        "(d(eprecated|o))",
        "(e(lse(if)?|nd(apply|autoescape|block|cache|embed|if|for|flush|macro|sandbox|verbatim|with)|mbed|xtends))",
        "(f(or|rom|lush ))",
        "(i(f|n(clude)?|mport))",
        "(macro)",
        "(or)",
        "(sandbox)",
        "(set)",
        "(use)",
        "(verbatim)",
        "(with)"
    ];

    public function highlight(string $input, string $prefix, string $suffix): string
    {
        $pattern = "/\b(" . implode("|", self::PATTERN) . ")\b/";

        return (string)preg_replace($pattern, $prefix . '$0' . $suffix, htmlspecialchars($input, ENT_QUOTES));
    }
}
