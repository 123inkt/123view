<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\GitCommitNotification\Request\Comment\CommentPreviewRequest;
use DR\GitCommitNotification\Tests\Unit\Request\AbstractRequestTestCase;

/**
 * @extends AbstractRequestTestCase<CommentPreviewRequest>
 * @coversDefaultClass \DR\GitCommitNotification\Request\Comment\CommentPreviewRequest
 * @covers ::__construct
 */
class CommentPreviewRequestTest extends AbstractRequestTestCase
{
    /**
     * @covers ::getState
     */
    public function testGetState(): void
    {
        $this->request->query->set('message', 'foobar');
        static::assertSame('foobar', $this->validatedRequest->getMessage());
    }

    /**
     * @covers ::getValidationRules
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
