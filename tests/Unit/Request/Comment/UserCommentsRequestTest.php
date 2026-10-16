<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\Comment\UserCommentsRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<UserCommentsRequest>
 */
#[CoversClass(UserCommentsRequest::class)]
class UserCommentsRequestTest extends AbstractRequestTestCase
{
    public function testGetSearchQuery(): void
    {
        static::assertSame('', $this->validatedRequest->getSearchQuery());

        $this->request->query->set('search', ' search ');
        static::assertSame('search', $this->validatedRequest->getSearchQuery());
    }

    public function testGetOrderBy(): void
    {
        static::assertSame('create-timestamp', $this->validatedRequest->getOrderBy());

        $this->request->query->set('order-by', 'update-timestamp');
        static::assertSame('update-timestamp', $this->validatedRequest->getOrderBy());
    }

    public function testGetPage(): void
    {
        static::assertSame(1, $this->validatedRequest->getPage());

        $this->request->query->set('page', '10');
        static::assertSame(10, $this->validatedRequest->getPage());
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
        return UserCommentsRequest::class;
    }
}
