<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Input;

use DR\GitCommitNotification\Input\AddExternalLinkInput;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Input\AddExternalLinkInput
 * @covers ::__construct
 */
class AddExternalLinkInputTest extends AbstractTestCase
{
    /**
     * @covers ::getUrl
     * @covers ::getPattern
     */
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

    /**
     * @covers ::getValidationRules
     */
    public function testGetValidationRules(): void
    {
        $validatedInput = new AddExternalLinkInput($this->createMock(InputInterface::class), new ConstraintViolationList());

        $definitions = $validatedInput->getValidationRules()->getDefinitions();
        static::assertArrayHasKey('arguments', $definitions);
        static::assertArrayNotHasKey('options', $definitions);
        static::assertCount(2, $definitions['arguments']);
    }
}
