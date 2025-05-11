<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Search;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\Search\SearchCodeRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<SearchCodeRequest>
 */
#[CoversClass(SearchCodeRequest::class)]
class SearchCodeRequestTest extends AbstractRequestTestCase
{
    public function testGetSearchQuery(): void
    {
        $this->request->query->set('search', 'query');
        static::assertSame('query', $this->validatedRequest->getSearchQuery());
    }

    public function testGetExtensions(): void
    {
        $this->request->query->set('extension', 'json,yaml');
        static::assertSame(['json', 'yaml'], $this->validatedRequest->getExtensions());
    }

    public function testGetExtensionsWithEmptyQuery(): void
    {
        static::assertNull($this->validatedRequest->getExtensions());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'query' => [
                    'search'    => 'required|string',
                    'extension' => 'string|regex:/^[a-zA-Z0-9]{1,5}(,[a-zA-Z0-9]{1,5})*$/',
                ]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return SearchCodeRequest::class;
    }
}
