<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Request\Comment;

use DigitalRevolution\SymfonyRequestValidation\Constraint\RequestConstraintFactory;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DigitalRevolution\SymfonyValidationShorthand\ConstraintFactory;
use DigitalRevolution\SymfonyValidationShorthand\Rule\InvalidRuleException;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Request\Comment\ChangeCommentStateRequest;
use DR\Review\Tests\Unit\Request\AbstractRequestTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Validator\Validation;

/**
 * @extends AbstractRequestTestCase<ChangeCommentStateRequest>
 */
#[CoversClass(ChangeCommentStateRequest::class)]
class ChangeCommentStateRequestTest extends AbstractRequestTestCase
{
    public function testGetState(): void
    {
        $this->request->request->set('state', 'resolved');
        static::assertSame(CommentStateEnum::Resolved, $this->validatedRequest->getState());
    }

    /**
     * @throws InvalidRuleException
     */
    public function testGetValidationRules(): void
    {
        $expected = new ValidationRules(
            [
                'request' => ['state' => 'required|string|in:' . implode(',', CommentStateEnum::values())]
            ]
        );
        $this->expectGetValidationRules($expected);

        $this->validatedRequest->validate();
    }

    public function testOpenIsAcceptedAndConverted(): void
    {
        $request = $this->createValidatedRequest('open');

        static::assertNull($request->validate());
        static::assertSame(CommentStateEnum::Open, $request->getState());
    }

    public function testResolvedIsAcceptedAndConverted(): void
    {
        $request = $this->createValidatedRequest('resolved');

        static::assertNull($request->validate());
        static::assertSame(CommentStateEnum::Resolved, $request->getState());
    }

    public function testUnknownStateFailsValidation(): void
    {
        $request = $this->createValidatedRequest('unknown');

        $this->expectException(BadRequestException::class);
        $request->validate();
    }

    protected static function getClassToTest(): string
    {
        return ChangeCommentStateRequest::class;
    }

    private function createValidatedRequest(string $state): ChangeCommentStateRequest
    {
        $request = new Request([], ['state' => $state]);
        $stack   = new RequestStack([$request]);

        return new ChangeCommentStateRequest($stack, Validation::createValidator(), new RequestConstraintFactory(new ConstraintFactory()));
    }
}
