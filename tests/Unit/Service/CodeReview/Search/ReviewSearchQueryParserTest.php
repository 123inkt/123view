<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Search;

use DR\Review\Service\CodeReview\Search\ReviewSearchQueryParser;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryParserFactory;
use DR\Review\Tests\AbstractTestCase;
use Parsica\Parsica\ParserHasFailed;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use function Parsica\Parsica\digitChar;
use function Parsica\Parsica\some;

#[CoversClass(ReviewSearchQueryParser::class)]
class ReviewSearchQueryParserTest extends AbstractTestCase
{
    private ReviewSearchQueryParserFactory&MockObject $parserFactory;
    private ReviewSearchQueryParser                   $queryParser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parserFactory = $this->createMock(ReviewSearchQueryParserFactory::class);
        $this->queryParser   = new ReviewSearchQueryParser($this->parserFactory);
    }

    /**
     * @throws ParserHasFailed
     */
    public function testParse(): void
    {
        $parser = some(digitChar());

        $this->parserFactory->expects($this->once())->method('createParser')->willReturn($parser);

        static::assertSame(['1', '2', '3'], $this->queryParser->parse('123')->output());
        static::assertSame(['4', '5', '6'], $this->queryParser->parse('456')->output());
    }
}
