<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Request\Comment\ChangeCommentStateRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;

/**
 * @extends AbstractRequestTestCase<ChangeCommentStateRequest>
 * @coversDefaultClass \DR\Review\Request\Comment\ChangeCommentStateRequest
 */
class ChangeCommentStateRequestTest extends AbstractRequestTestCase
{
    /**
     * @covers ::getState
     */
    public function testGetState(): void
    {
        $this->request->request->set('state', 'foobar');
        static::assertSame('foobar', $this->validatedRequest->getState());
    }

    /**
     * @covers ::getValidationRules
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'request' => ['state' => 'required|string|in:' . implode(',', CommentStateType::VALUES)]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return ChangeCommentStateRequest::class;
    }
}
