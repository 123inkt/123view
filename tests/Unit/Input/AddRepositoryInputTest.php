<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Input;

use DR\GitCommitNotification\Input\AddRepositoryInput;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Validator\ConstraintViolationList;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Input\AddRepositoryInput
 * @covers ::__construct
 */
class AddRepositoryInputTest extends AbstractTestCase
{
    /**
     * @covers ::getRepository
     * @covers ::getName
     * @covers ::getGitlabId
     * @covers ::getUpsourceId
     */
    public function testGetRepository(): void
    {
        $definition     = new InputDefinition(
            [
                new InputArgument('repository', InputArgument::REQUIRED),
                new InputOption('name'),
                new InputOption('upsource'),
                new InputOption('gitlab'),
            ]
        );
        $input          = new ArrayInput(
            [
                'repository' => 'repository',
                '--name'     => 'name',
                '--upsource' => 'upsource',
                '--gitlab'   => '123'
            ],
            $definition
        );
        $validatedInput = new AddRepositoryInput($input, new ConstraintViolationList());

        static::assertSame('repository', $validatedInput->getRepository());
        static::assertSame('name', $validatedInput->getName());
        static::assertSame('upsource', $validatedInput->getUpsourceId());
        static::assertSame(123, $validatedInput->getGitlabId());
    }

    /**
     * @covers ::getRepository
     * @covers ::getName
     * @covers ::getGitlabId
     * @covers ::getUpsourceId
     */
    public function testGetRepositoryGetNameFromRepository(): void
    {
        $definition     = new InputDefinition(
            [
                new InputArgument('repository', InputArgument::REQUIRED),
                new InputOption('name'),
                new InputOption('upsource'),
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
        static::assertCount(3, $definitions['options']);
    }
}
