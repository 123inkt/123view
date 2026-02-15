<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Input;

use DR\Review\Input\AddExternalLinkInput;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Validator\ConstraintViolationList;

#[CoversClass(AddExternalLinkInput::class)]
class AddExternalLinkInputTest extends AbstractTestCase
{
    public function testGetUrl(): void
    {
        $definition     = new InputDefinition(
            [
                new InputArgument('pattern', InputArgument::REQUIRED),
                new InputArgument('url', InputArgument::REQUIRED)
            ]
        );
        $input          = new ArrayInput(['pattern' => 'pattern', 'url' => 'https://url/'], $definition);
        $validatedInput = new AddExternalLinkInput($input, new ConstraintViolationList());

        static::assertSame('pattern', $validatedInput->getPattern());
        static::assertSame('https://url/', $validatedInput->getUrl());
    }

    public function testGetValidationRules(): void
    {
        $validatedInput = new AddExternalLinkInput(static::createStub(InputInterface::class), new ConstraintViolationList());

        $definitions = $validatedInput->getValidationRules()->getDefinitions();
        static::assertArrayHasKey('arguments', $definitions);
        static::assertArrayNotHasKey('options', $definitions);
        static::assertCount(2, $definitions['arguments']);
    }
}
