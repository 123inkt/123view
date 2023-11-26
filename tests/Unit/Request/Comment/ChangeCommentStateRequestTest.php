<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Request\Comment\ChangeCommentStateRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<ChangeCommentStateRequest>
 */
#[CoversClass(ChangeCommentStateRequest::class)]
class ChangeCommentStateRequestTest extends AbstractRequestTestCase
{
    public function testGetState(): void
    {
        $this->request->request->set('state', 'foobar');
        static::assertSame('foobar', $this->validatedRequest->getState());
    }

    /**
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
