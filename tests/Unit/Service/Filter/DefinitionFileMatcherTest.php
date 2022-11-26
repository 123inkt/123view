<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use DR\GitCommitNotification\Entity\Config\Filter;
use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Service\Filter\DefinitionFileMatcher;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use RuntimeException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Filter\DefinitionFileMatcher
 */
class DefinitionFileMatcherTest extends AbstractTestCase
{
    /**
     * @covers ::matches
     */
    public function testMatchesEmptyDiffFileShouldNotMatch(): void
    {
        $file       = new DiffFile();
        $filter = new Filter();

        $matcher = new DefinitionFileMatcher();
        static::assertFalse($matcher->matches($file, new ArrayCollection([$filter])));
    }

    /**
     * @covers ::matches
     */
    public function testMatchesShouldMatchDefinition(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = '/path/to/file.txt';

        $filter = new Filter();
        $filter->setPattern('#file\\.txt$#');

        $matcher = new DefinitionFileMatcher();
        static::assertTrue($matcher->matches($file, new ArrayCollection([$filter])));
    }

    /**
     * @covers ::matches
     */
    public function testMatchesShouldNotMatchDefinition(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = '/path/to/foobar.txt';

        $filter = new Filter();
        $filter->setPattern('#file\\.txt$#');

        $matcher = new DefinitionFileMatcher();
        static::assertFalse($matcher->matches($file, new ArrayCollection([$filter])));
    }

    /**
     * @covers ::matches
     */
    public function testMatchesShouldThrowExceptionOnInvalidRegex(): void
    {
        $file                = new DiffFile();
        $file->filePathAfter = '/path/to/foobar.txt';

        $filter = new Filter();
        $filter->setPattern('#invalid');

        $matcher = new DefinitionFileMatcher();

        $this->expectException(RuntimeException::class);
        $matcher->matches($file, new ArrayCollection([$filter]));
    }
}
