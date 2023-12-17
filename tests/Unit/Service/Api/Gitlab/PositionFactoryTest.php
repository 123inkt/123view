<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Model\Api\Gitlab\Version;
use DR\Review\Service\Api\Gitlab\PositionFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PositionFactory::class)]
class PositionFactoryTest extends AbstractTestCase
{
    private PositionFactory $factory;
    private Version         $version;

    protected function setUp(): void
    {
        parent::setUp();
        $this->version                 = new Version();
        $this->version->baseCommitSha  = 'base';
        $this->version->startCommitSha = 'start';
        $this->version->headCommitSha  = 'head';
        $this->factory                 = new PositionFactory();
    }

    public function testCreateLineAdded(): void
    {
        $lineReference = new LineReference('oldPath', 'newPath', 111, 222, 333, 'headSha', LineReferenceStateEnum::Added);
        $position      = $this->factory->create($this->version, $lineReference);

        static::assertSame('text', $position->positionType);
        static::assertSame('headSha', $position->headSha);
        static::assertSame('start', $position->startSha);
        static::assertSame('base', $position->baseSha);
        static::assertSame('oldPath', $position->oldPath);
        static::assertSame('newPath', $position->newPath);
        static::assertNull($position->oldLine);
        static::assertSame(333, $position->newLine);
    }

    public function testCreateLineModified(): void
    {
        $lineReference = new LineReference('oldPath', 'newPath', 111, 222, 333, 'headSha', LineReferenceStateEnum::Modified);
        $position      = $this->factory->create($this->version, $lineReference);

        static::assertNull($position->oldLine);
        static::assertSame(333, $position->newLine);
    }

    public function testCreateLineDeleted(): void
    {
        $lineReference = new LineReference('oldPath', 'newPath', 111, 222, 333, 'headSha', LineReferenceStateEnum::Deleted);
        $position      = $this->factory->create($this->version, $lineReference);

        static::assertNull($position->newLine);
        static::assertSame(111, $position->oldLine);
    }

    public function testCreateLineUnchanged(): void
    {
        $lineReference = new LineReference('oldPath', 'newPath', 111, 222, 333, 'headSha', LineReferenceStateEnum::Unmodified);
        $position      = $this->factory->create($this->version, $lineReference);

        static::assertSame(111, $position->oldLine);
        static::assertSame(333, $position->newLine);
    }
}
