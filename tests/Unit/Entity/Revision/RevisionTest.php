<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Revision;

use DigitalRevolution\AccessorPairConstraint\Constraint\ConstraintConfig;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Uid\UuidV7;

#[CoversClass(Revision::class)]
class RevisionTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        $config = (new ConstraintConfig())->setExcludedMethods(['getSort', 'setSort']);

        static::assertNull((new Revision())->getId());
        static::assertAccessorPairs(Revision::class, $config);
    }

    public function testSort(): void
    {
        $uuid     = UuidV7::generate();
        $revision = new Revision();

        $revision->setSort(null);
        static::assertNull($revision->getSort());

        $revision->setSort($uuid);
        static::assertSame($uuid, $revision->getSort());
    }
}
