<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Input;

use DR\Review\ApiPlatform\Input\UpdateCommentInput;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\Validation;

#[CoversClass(UpdateCommentInput::class)]
class UpdateCommentInputTest extends AbstractTestCase
{
    public function testTracksWhetherMessageWasProvided(): void
    {
        $input = new UpdateCommentInput();

        static::assertFalse($input->hasMessage());
        static::assertFalse($input->hasChanges());

        $input->message = 'Updated comment';

        static::assertTrue($input->hasMessage());
        static::assertTrue($input->hasChanges());
    }

    public function testTracksWhetherStateWasProvided(): void
    {
        $input = new UpdateCommentInput();

        static::assertFalse($input->hasState());
        static::assertFalse($input->hasChanges());

        $input->state = CommentStateEnum::Resolved;

        static::assertTrue($input->hasState());
        static::assertTrue($input->hasChanges());
    }

    public function testTracksWhetherTagWasProvidedAndReturnsItsValue(): void
    {
        $input = new UpdateCommentInput();

        static::assertFalse($input->hasTag());
        static::assertSame($input, $input->setTag(CommentTagEnum::Suggestion));
        static::assertTrue($input->hasTag());
        static::assertSame(CommentTagEnum::Suggestion, $input->getTag());

        $input->setTag(null);

        static::assertTrue($input->hasTag());
        static::assertNull($input->getTag());
        static::assertTrue($input->hasChanges());
    }

    public function testEmptyInputIsInvalid(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        static::assertNotEmpty($validator->validate(new UpdateCommentInput()));
    }

    public function testValidMessagePassesValidation(): void
    {
        $input          = new UpdateCommentInput();
        $input->message = 'Updated comment';
        $validator      = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        static::assertEmpty($validator->validate($input));
    }

    public function testMessageMustNotBeBlank(): void
    {
        $input          = new UpdateCommentInput();
        $input->message = '   ';
        $validator      = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        static::assertNotEmpty($validator->validate($input));
    }

    public function testMessageCannotExceedMaximumLength(): void
    {
        $input          = new UpdateCommentInput();
        $input->message = str_repeat('a', Comment::MAX_COMMENT_LENGTH + 1);
        $validator      = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        static::assertNotEmpty($validator->validate($input));
    }
}
