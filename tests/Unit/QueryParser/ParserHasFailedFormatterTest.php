<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\QueryParser;

use DR\Review\QueryParser\InvalidQueryException;
use DR\Review\QueryParser\ParserHasFailedFormatter;
use DR\Review\Tests\AbstractTestCase;
use Parsica\Parsica\ParserHasFailed;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Parsica\Parsica\atLeastOne;
use function Parsica\Parsica\isDigit;
use function Parsica\Parsica\satisfy;

#[CoversClass(ParserHasFailedFormatter::class)]
class ParserHasFailedFormatterTest extends AbstractTestCase
{
    private TranslatorInterface&MockObject $translator;
    private ParserHasFailedFormatter       $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->formatter  = new ParserHasFailedFormatter($this->translator);
    }

    public function testFormatEof(): void
    {
        $this->translator->expects($this->exactly(2))->method('trans')->willReturn('unexpected', 'expected');

        try {
            atLeastOne(satisfy(isDigit()))->tryString('');
        } catch (ParserHasFailed $exception) {
            static::assertSame('unexpected <EOF>. expected satisfy(predicate).', $this->formatter->format(new InvalidQueryException($exception)));
        }
    }

    public function testFormat(): void
    {
        $this->translator->expects($this->exactly(2))->method('trans')->willReturn('unexpected', 'expected');

        try {
            atLeastOne(satisfy(isDigit()))->tryString('foobar');
        } catch (ParserHasFailed $exception) {
            static::assertSame('unexpected \'f\'. expected satisfy(predicate).', $this->formatter->format(new InvalidQueryException($exception)));
        }
    }
}
