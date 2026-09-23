<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Input;

use DR\Review\ApiPlatform\Input\UpdateCommentInput;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Validator\Validation;

#[CoversClass(UpdateCommentInput::class)]
class UpdateCommentInputTest extends AbstractTestCase
{
    public function testHasChanged(): void
    {
        $input = new UpdateCommentInput();
        static::assertFalse($input->hasChanges());
        $input->setTag(null);
        static::assertTrue($input->hasChanges());
    }

    public function testEmptyInputIsInvalid(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        static::assertNotEmpty($validator->validate(new UpdateCommentInput()));
    }
}
