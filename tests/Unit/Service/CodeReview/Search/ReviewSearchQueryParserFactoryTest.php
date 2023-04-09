<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Search;

use DR\Review\Service\CodeReview\Search\ReviewSearchQueryParserFactory;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;

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
    public function testCreateParser(): void
    {
        $result = (string)$this->parserFactory->createParser()->tryString("id:123")->output();
    }
}
