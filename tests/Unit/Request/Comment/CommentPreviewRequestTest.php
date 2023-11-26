<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Request\Comment\CommentPreviewRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @extends AbstractRequestTestCase<CommentPreviewRequest>
 */
#[CoversClass(CommentPreviewRequest::class)]
class CommentPreviewRequestTest extends AbstractRequestTestCase
{
    public function testGetState(): void
    {
        $this->request->query->set('message', 'foobar');
        static::assertSame('foobar', $this->validatedRequest->getMessage());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(['query' => ['message' => 'required|string|filled|max:2000']]);
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    protected static function getClassToTest(): string
    {
        return CommentPreviewRequest::class;
    }
}
