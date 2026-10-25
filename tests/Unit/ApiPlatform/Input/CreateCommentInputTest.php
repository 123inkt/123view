<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Input;

use DR\Review\ApiPlatform\Input\CreateCommentInput;
use DR\Review\Entity\Review\Comment;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validation;

#[CoversClass(CreateCommentInput::class)]
class CreateCommentInputTest extends AbstractTestCase
{
    public function testValidInputPassesValidation(): void
    {
        $input = $this->createValidInput();

        static::assertNull($input->tag);
        static::assertEmpty($this->validateInput($input));
    }

    public function testMaximumAllowedLengthsPassValidation(): void
    {
        $input           = $this->createValidInput();
        $input->message  = str_repeat('m', Comment::MAX_COMMENT_LENGTH);
        $input->filepath = str_repeat('f', 500);

        static::assertEmpty($this->validateInput($input));
    }

    public function testMessageMustNotBeBlank(): void
    {
        $input          = $this->createValidInput();
        $input->message = " \t ";

        $this->assertViolationForProperty('message', $input);
    }

    public function testMessageCannotExceedMaximumLength(): void
    {
        $input          = $this->createValidInput();
        $input->message = str_repeat('m', Comment::MAX_COMMENT_LENGTH + 1);

        $this->assertViolationForProperty('message', $input);
    }

    public function testFilepathMustNotBeBlank(): void
    {
        $input           = $this->createValidInput();
        $input->filepath = '   ';

        $this->assertViolationForProperty('filepath', $input);
    }

    public function testFilepathCannotExceedMaximumLength(): void
    {
        $input           = $this->createValidInput();
        $input->filepath = str_repeat('f', 501);

        $this->assertViolationForProperty('filepath', $input);
    }

    public function testLineMustBePositive(): void
    {
        foreach ([0, -1] as $line) {
            $input       = $this->createValidInput();
            $input->line = $line;

            $this->assertViolationForProperty('line', $input);
        }
    }

    private function createValidInput(): CreateCommentInput
    {
        $input           = new CreateCommentInput();
        $input->message  = 'A comment';
        $input->filepath = 'src/File.php';
        $input->line     = 1;

        return $input;
    }

    private function validateInput(CreateCommentInput $input): ConstraintViolationListInterface
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        return $validator->validate($input);
    }

    private function assertViolationForProperty(string $property, CreateCommentInput $input): void
    {
        $violations = $this->validateInput($input);

        static::assertCount(1, $violations);
        static::assertSame($property, $violations->get(0)->getPropertyPath());
    }
}
