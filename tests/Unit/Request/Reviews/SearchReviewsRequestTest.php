<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Reviews;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\Reviews\SearchReviewsRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<SearchReviewsRequest>
 */
#[CoversClass(SearchReviewsRequest::class)]
class SearchReviewsRequestTest extends AbstractRequestTestCase
{
    public function testGetPage(): void
    {
        $this->request->query->set('page', '10');
        static::assertSame(10, $this->validatedRequest->getPage());
    }

    public function testGetOrderBy(): void
    {
        $this->request->query->set('order-by', 'create-timestamp');
        static::assertSame('create-timestamp', $this->validatedRequest->getOrderBy());
    }

    public function testGetSearchQuery(): void
    {
        $this->request->query->set('search', 'search');
        static::assertSame('search', $this->validatedRequest->getSearchQuery());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'query' => [
                    'search'   => 'string',
                    'order-by' => 'string|in:create-timestamp,update-timestamp',
                    'page'     => 'integer|min:1'
                ],
            ],
            true
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return SearchReviewsRequest::class;
    }
}
