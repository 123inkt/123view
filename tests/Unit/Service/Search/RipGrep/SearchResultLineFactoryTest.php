<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Search\RipGrep;

use DR\Review\Model\Search\SearchResultLine;
use DR\Review\Model\Search\SearchResultLineTypeEnum;
use DR\Review\Service\Search\RipGrep\SearchResultLineFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SearchResultLineFactory::class)]
class SearchResultLineFactoryTest extends AbstractTestCase
{
    private SearchResultLineFactory $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->factory = new SearchResultLineFactory();
    }

    public function testCreateContextFromEntry(): void
    {
        $expected = new SearchResultLine('line', 123, SearchResultLineTypeEnum::Context);
        $result   = $this->factory->createContextFromEntry(['data' => ['lines' => ['text' => 'line'], 'line_number' => 123]]);
        static::assertEquals($expected, $result);
    }

    public function testCreateMatchFromEntry(): void
    {
        $expected = new SearchResultLine('line', 123, SearchResultLineTypeEnum::Match);
        $result   = $this->factory->createMatchFromEntry(['data' => ['lines' => ['text' => 'line'], 'line_number' => 123]]);
        static::assertEquals($expected, $result);
    }
}
