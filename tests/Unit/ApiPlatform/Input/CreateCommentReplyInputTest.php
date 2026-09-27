<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Input;

use DR\Review\ApiPlatform\Input\CreateCommentReplyInput;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

#[CoversClass(CreateCommentReplyInput::class)]
class CreateCommentReplyInputTest extends AbstractTestCase
{
    public function testValidInputPassesValidation(): void
    {
        $input      = $this->createValidInput();
        $input->tag = CommentTagEnum::Suggestion;

        static::assertEmpty($this->validateInput($input));
    }

    public function testMaximumMessageLengthPassesValidation(): void
    {
        $input          = $this->createValidInput();
        $input->message = str_repeat('m', Comment::MAX_COMMENT_LENGTH);

        static::assertEmpty($this->validateInput($input));
    }

    public function testMessageMustNotBeBlank(): void
    {
        $input          = $this->createValidInput();
        $input->message = " \t ";

        $this->assertViolationForMessage($input);
    }

    public function testMessageCannotExceedMaximumLength(): void
    {
        $input          = $this->createValidInput();
        $input->message = str_repeat('m', Comment::MAX_COMMENT_LENGTH + 1);

        $this->assertViolationForMessage($input);
    }

    private function createValidInput(): CreateCommentReplyInput
    {
        $input          = new CreateCommentReplyInput();
        $input->message = 'A reply';

        return $input;
    }

    private function validateInput(CreateCommentReplyInput $input): ConstraintViolationListInterface
    {
        return Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator()->validate($input);
    }

    private function assertViolationForMessage(CreateCommentReplyInput $input): void
    {
        $violations = $this->validateInput($input);

        static::assertCount(1, $violations);
        static::assertSame('message', $violations->get(0)->getPropertyPath());
    }
}
