<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Twig\Highlight;

use DR\GitCommitNotification\Service\CodeTokenizer\CodeTokenizer;

class PHPHighlighter implements HighlighterInterface
{
    public const EXTENSION = 'php';

    private const PATTERN = [
        "(a(bstract|nd|rray|s))",
        "(bool)",
        "(c(a(llable|se|tch)|l(ass|one)|on(st|tinue)))",
        "(d(e(clare|fault)|ie|o))",
        "(e(cho|lse(if)?|mpty|nd(declare|for(each)?|if|switch|while)|val|x(it|tends)))",
        "(f(inal|or(each)?|unction|alse|loat))",
        "(g(lobal|oto))",
        "(i(f|mplements|n(clude(_once)?|st(anceof|eadof)|terface)|sset|nt))",
        "(n(amespace|ew|ull))",
        "(p(r(i(nt|vate)|otected)|ublic))",
        "(re(quire(_once)?|turn))",
        "(s(tatic|witch|tring))",
        "(t(hrow|r(ait|y|ue)))",
        "(u(nset|se))",
        "(__halt_compiler|break|list|(x)?or|var|while)",
    ];

    public function __construct(private readonly HighlightPattern $pattern, private readonly CodeTokenizer $tokenizer)
    {
    }

    public function highlight(string $input, string $prefix, string $suffix): string
    {
        if ($input === '') {
            return $input;
        }

        $result = [];
        foreach ($this->tokenizer->tokenize($input) as [$token, $value]) {
            if ($token === CodeTokenizer::TOKEN_CODE) {
                $result[] = preg_replace_callback(
                    "/\b(" . implode("|", self::PATTERN) . ")\b/",
                    fn($matches) => sprintf($this->pattern->keyword, $matches[0]),
                    htmlspecialchars($value, ENT_QUOTES)
                );
            } elseif ($token === CodeTokenizer::TOKEN_STRING) {
                $result[] = sprintf($this->pattern->string, htmlspecialchars($value, ENT_QUOTES));
            } elseif ($token === CodeTokenizer::TOKEN_COMMENT) {
                $result[] = sprintf($this->pattern->comment, htmlspecialchars($value, ENT_QUOTES));
            }
        }

        return implode('', $result);
    }
}
