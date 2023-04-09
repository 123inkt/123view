<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Search;

use DR\Review\Service\CodeReview\Search\ReviewSearchQueryParserFactory;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(ReviewSearchQueryParserFactory::class)]
class ReviewSearchQueryParserFactoryTest extends AbstractTestCase
{
    private ReviewSearchQueryParserFactory $parserFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parserFactory = new ReviewSearchQueryParserFactory();
    }

    /**
     * @throws Exception
     */
    #[DataProvider('dataProvider')]
    public function testCreateParser(string $query, string $expected): void
    {
        $result = $this->parserFactory->createParser()->tryString($query);

        static::assertSame("", (string)$result->remainder());
        static::assertSame($expected, (string)$result->output());
    }

    /**
     * @return array<array-key,array{string,string}>
     */
    public static function dataProvider(): array
    {
        return [
            // single words
            ['id:5', 'id:"5"'],
            ['state:open', 'state:"open"'],
            ['author:sherlock', 'author:"sherlock"'],
            ['reviewer:sherlock', 'reviewer:"sherlock"'],
            ['search', '"search"'],
            // not operator
            ['not id:5', 'NOT (id:"5")'],
            // explicit and operators
            ['id:5 and state:open', '(id:"5") AND (state:"open")'],
            ['id:5 and state:open and author:sherlock', '((id:"5") AND (state:"open")) AND (author:"sherlock")'],
            // implicit and operators
            ['id:5 state:open', '(id:"5") AND (state:"open")'],
            ['id:5 state:open author:sherlock', '(id:"5") AND ((state:"open") AND (author:"sherlock"))'],
            // implicit and, or operators
            ['id:5 and state:open or author:sherlock', '((id:"5") AND (state:"open")) OR (author:"sherlock")'],
            // explicit and, or operators
            ['id:5 and (state:open or author:sherlock)', '(id:"5") AND ((state:"open") OR (author:"sherlock"))'],
            ['id:5 and not author:sherlock', '(id:"5") AND (NOT (author:"sherlock"))'],
        ];
    }
}
