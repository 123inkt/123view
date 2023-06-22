<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\User;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\User\UserMentionSuggestRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<UserMentionSuggestRequest>
 */
#[CoversClass(UserMentionSuggestRequest::class)]
class UserMentionSuggestRequestTest extends AbstractRequestTestCase
{
    public function testGetSearch(): void
    {
        $this->request->query->set('search', 'searchQuery');
        static::assertSame('searchQuery', $this->validatedRequest->getSearch());
    }

    public function testGetPreferredUserIds(): void
    {
        $this->request->query->set('preferredUserIds', '');
        static::assertSame([], $this->validatedRequest->getPreferredUserIds());

        $this->request->query->set('preferredUserIds', '1,2,3');
        static::assertSame([1, 2, 3], $this->validatedRequest->getPreferredUserIds());
    }

    /**
     * @covers ::getValidationRules
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'query' => [
                    'search'           => 'string',
                    'preferredUserIds' => 'string|regex:/^(\d+(,\d+)*)?$/'
                ]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return UserMentionSuggestRequest::class;
    }
}
