<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig\Highlight;

class TypescriptHighlighter implements HighlighterInterface
{
    public const EXTENSION = 'ts';

    private const PATTERN = [
        "(b(reak|oolean))",
        "(c(ase|atch|lass|onst|ontinue))",
        "(d(e(bugger|fault|lete|scribe)|o))",
        "(e(lse|num|x(port|tends)))",
        "(f(alse|inally|or|unction|rom))",
        "(i(f|mport|n|nstanceof))",
        "(n(ew|ull|umber))",
        "(r(eturn|eadonly))",
        "(s(uper|witch|tring))",
        "(t(his|hrow|rue|ry|ypeof))",
        "(object)",
        "(public)",
        "(v(ar|oid))",
        "(w(hile|ith))"
    ];

    public function highlight(string $input, string $prefix, string $suffix): string
    {
        $pattern = "/\b(" . implode("|", self::PATTERN) . ")\b/";

        return (string)preg_replace($pattern, $prefix . '$0' . $suffix, $input);
    }
}
