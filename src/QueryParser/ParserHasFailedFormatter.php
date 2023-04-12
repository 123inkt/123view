<?php
declare(strict_types=1);

namespace DR\Review\QueryParser;

use Parsica\Parsica\Internal\Ascii;
use Parsica\Parsica\Internal\EndOfStream;
use Symfony\Contracts\Translation\TranslatorInterface;

class ParserHasFailedFormatter
{
    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    public function format(InvalidQueryException $failure): string
    {
        $parseResult = $failure->parseResult();
        try {
            $unexpected = Ascii::printable($parseResult->got()->take1()->chunk());
        } catch (EndOfStream) {
            $unexpected = "<EOF>";
        }

        $expected = $failure->parseResult()->expected();

        return sprintf(
            "%s %s. %s %s.",
            $this->translator->trans('unexpected'),
            $unexpected,
            $this->translator->trans('expecting'),
            $expected
        );
    }
}
