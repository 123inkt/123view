<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Filter;

use DR\GitCommitNotification\Entity\Config\Definition;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Service\Filter\DefinitionFileMatcher;
use DR\GitCommitNotification\Tests\AbstractTest;
use RuntimeException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Filter\DefinitionFileMatcher
 */
class DefinitionFileMatcherTest extends AbstractTest
{
    /**
     * @covers ::matches
     */
    public function testMatchesEmptyDiffFileShouldNotMatch(): void
    {
        $file       = new DiffFile();
        $definition = new Definition();

        $matcher = new DefinitionFileMatcher();
        static::assertFalse($matcher->matches($file, $definition));
    }

    /**
     * @covers ::matches
     */
    public function testMatchesShouldMatchDefinition(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = '/path/to/file.txt';

        $definition = new Definition();
        $definition->addFile('#file\\.txt$#');

        $matcher = new DefinitionFileMatcher();
        static::assertTrue($matcher->matches($file, $definition));
    }

    /**
     * @covers ::matches
     */
    public function testMatchesShouldNotMatchDefinition(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = '/path/to/foobar.txt';

        $definition = new Definition();
        $definition->addFile('#file\\.txt$#');

        $matcher = new DefinitionFileMatcher();
        static::assertFalse($matcher->matches($file, $definition));
    }

    /**
     * @covers ::matches
     */
    public function testMatchesShouldThrowExceptionOnInvalidRegex(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = '/path/to/foobar.txt';

        $definition = new Definition();
        $definition->addFile('#invalid');

        $matcher = new DefinitionFileMatcher();

        $this->expectException(RuntimeException::class);
        $matcher->matches($file, $definition);
    }
}
