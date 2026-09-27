<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Input;

use DR\Review\ApiPlatform\Input\UpdateCommentReplyInput;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\Validation;

#[CoversClass(UpdateCommentReplyInput::class)]
class UpdateCommentReplyInputTest extends AbstractTestCase
{
    public function testTracksWhetherMessageWasProvided(): void
    {
        $input = new UpdateCommentReplyInput();

        static::assertFalse($input->hasMessage());
        static::assertFalse($input->hasChanges());

        $input->message = 'Updated reply';

        static::assertTrue($input->hasMessage());
        static::assertTrue($input->hasChanges());
    }

    public function testTracksProvidedTagAndItsValue(): void
    {
        $input = new UpdateCommentReplyInput();

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

        static::assertNotEmpty($validator->validate(new UpdateCommentReplyInput()));
    }

    public function testValidMessagePassesValidation(): void
    {
        $input          = new UpdateCommentReplyInput();
        $input->message = 'Updated reply';
        $validator      = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        static::assertEmpty($validator->validate($input));
    }

    public function testMessageMustNotBeBlank(): void
    {
        $input          = new UpdateCommentReplyInput();
        $input->message = '   ';
        $validator      = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        static::assertNotEmpty($validator->validate($input));
    }

    public function testMessageCannotExceedMaximumLength(): void
    {
        $input          = new UpdateCommentReplyInput();
        $input->message = str_repeat('a', Comment::MAX_COMMENT_LENGTH + 1);
        $validator      = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        static::assertNotEmpty($validator->validate($input));
    }
}
