<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Input;

use DR\Review\Input\AddRepositoryInput;
use DR\Review\Tests\AbstractTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @coversDefaultClass \DR\Review\Input\AddRepositoryInput
 */
class AddRepositoryInputTest extends AbstractTestCase
{
    /**
     * @covers ::getRepository
     * @covers ::getName
     * @covers ::getGitlabId
     */
    public function testGetRepository(): void
    {
        $definition     = new InputDefinition(
            [
                new InputArgument('repository', InputArgument::REQUIRED),
                new InputOption('name'),
                new InputOption('gitlab'),
            ]
        );
        $input          = new ArrayInput(
            [
                'repository' => 'repository',
                '--name'     => 'name',
                '--gitlab'   => '123'
            ],
            $definition
        );
        $validatedInput = new AddRepositoryInput($input, new ConstraintViolationList());

        static::assertSame('repository', $validatedInput->getRepository());
        static::assertSame('name', $validatedInput->getName());
        static::assertSame(123, $validatedInput->getGitlabId());
    }

    /**
     * @covers ::getRepository
     * @covers ::getName
     * @covers ::getGitlabId
     */
    public function testGetRepositoryGetNameFromRepository(): void
    {
        $definition     = new InputDefinition(
            [
                new InputArgument('repository', InputArgument::REQUIRED),
                new InputOption('name'),
                new InputOption('gitlab'),
            ]
        );
        $input          = new ArrayInput(['repository' => '/url/to/repository.git',], $definition);
        $validatedInput = new AddRepositoryInput($input, new ConstraintViolationList());

        static::assertSame('/url/to/repository.git', $validatedInput->getRepository());
        static::assertSame('repository', $validatedInput->getName());
    }

    /**
     * @covers ::getValidationRules
     */
    public function testGetValidationRules(): void
    {
        $validatedInput = new AddRepositoryInput($this->createMock(InputInterface::class), new ConstraintViolationList());

        $definitions = $validatedInput->getValidationRules()->getDefinitions();
        static::assertArrayHasKey('arguments', $definitions);
        static::assertArrayHasKey('options', $definitions);
        static::assertCount(1, $definitions['arguments']);
        static::assertCount(2, $definitions['options']);
    }
}
