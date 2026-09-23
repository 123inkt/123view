<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Input;

use DR\Review\ApiPlatform\Input\UpdateCommentInput;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

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
}
