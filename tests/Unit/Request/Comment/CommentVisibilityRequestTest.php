<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Entity\Review\CommentVisibilityEnum;
use DR\Review\Request\Comment\CommentVisibilityRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<CommentVisibilityRequest>
 */
#[CoversClass(CommentVisibilityRequest::class)]
class CommentVisibilityRequestTest extends AbstractRequestTestCase
{
    public function testGetState(): void
    {
        $this->request->request->set('visibility', 'unresolved');
        static::assertSame(CommentVisibilityEnum::UNRESOLVED, $this->validatedRequest->getVisibility());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(['request' => ['visibility' => 'required|string|in:all,unresolved,none']]);
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return CommentVisibilityRequest::class;
    }
}
