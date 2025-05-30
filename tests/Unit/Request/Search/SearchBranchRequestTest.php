<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Search;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\Search\SearchBranchRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<SearchBranchRequest>
 */
#[CoversClass(SearchBranchRequest::class)]
class SearchBranchRequestTest extends AbstractRequestTestCase
{
    public function testGetSearchQuery(): void
    {
        $this->request->query->set('search', 'query');
        static::assertSame('query', $this->validatedRequest->getSearchQuery());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(['query' => ['search' => 'string']]);
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return SearchBranchRequest::class;
    }
}
