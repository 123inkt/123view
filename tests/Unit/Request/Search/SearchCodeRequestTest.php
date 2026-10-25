<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Search;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Model\Search\SearchFilter;
use DR\Review\Request\Search\SearchCodeRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<SearchCodeRequest>
 */
#[CoversClass(SearchCodeRequest::class)]
class SearchCodeRequestTest extends AbstractRequestTestCase
{
    public function testGetFilter(): void
    {
        $this->request->query->set('search', 'query');
        $this->request->query->set('filename', 'composer\\.json');
        $this->request->query->set('regex', 'true');

        static::assertEquals(new SearchFilter('query', 'composer\\.json', true), $this->validatedRequest->getFilter());
    }

    public function testGetFilterWithoutOptionalValues(): void
    {
        $this->request->query->set('search', 'query');

        static::assertEquals(new SearchFilter('query', null, false), $this->validatedRequest->getFilter());
    }

    public function testGetIsShowAll(): void
    {
        $this->request->query->set('all', 'true');
        static::assertTrue($this->validatedRequest->isShowAll());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'query' => [
                    'search'   => 'required|string',
                    'filename' => 'string',
                    'all'      => 'string',
                    'regex'    => 'string|in:true,false',
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
