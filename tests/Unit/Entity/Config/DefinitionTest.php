<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Entity\Config;

use DR\GitCommitNotification\Entity\Config\Definition;
use DR\GitCommitNotification\Tests\AbstractTest;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Entity\Config\Definition
 */
class DefinitionTest extends AbstractTest
{
    /**
     * @covers ::getSubjects
     * @covers ::addSubject
     */
    public function testGetSubjects(): void
    {
        $definition = new Definition();
        static::assertEmpty($definition->getSubjects());

        $definition->addSubject('foobar');
        static::assertSame(['foobar'], $definition->getSubjects());
    }

    /**
     * @covers ::getAuthors
     * @covers ::addAuthor
     */
    public function testGetAuthors(): void
    {
        $definition = new Definition();
        static::assertEmpty($definition->getAuthors());

        $definition->addAuthor('foobar');
        static::assertSame(['foobar'], $definition->getAuthors());
    }

    /**
     * @covers ::getFiles
     * @covers ::addFile
     */
    public function testGetFiles(): void
    {
        $definition = new Definition();
        static::assertEmpty($definition->getFiles());

        $definition->addFile('file');
        static::assertSame(['file'], $definition->getFiles());
    }
}
