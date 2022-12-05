<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Filter;

use Doctrine\Common\Collections\ArrayCollection;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Notification\Filter;
use DR\Review\Service\Filter\DefinitionFileMatcher;
use DR\Review\Tests\AbstractTestCase;
use RuntimeException;

/**
 * @coversDefaultClass \DR\Review\Service\Filter\DefinitionFileMatcher
 */
class DefinitionFileMatcherTest extends AbstractTestCase
{
    /**
     * @covers ::matches
     */
    public function testMatchesEmptyDiffFileShouldNotMatch(): void
    {
        $file   = new DiffFile();
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
