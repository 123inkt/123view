<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Search;

use DR\Review\QueryParser\InvalidQueryException;
use DR\Review\QueryParser\Term\EmptyMatch;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryParserFactory;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryTermFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewSearchQueryTermFactory::class)]
class ReviewSearchQueryTermFactoryTest extends AbstractTestCase
{
    private ReviewSearchQueryTermFactory $termFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $parserFactory     = static::createStub(ReviewSearchQueryParserFactory::class);
        $this->termFactory = new ReviewSearchQueryTermFactory($parserFactory);
    }

    /**
     * @throws InvalidQueryException
     */
    public function testGetSearchTermsShouldSkipEmptyString(): void
    {
        static::assertInstanceOf(EmptyMatch::class, $this->termFactory->getSearchTerms(''));
    }
}
