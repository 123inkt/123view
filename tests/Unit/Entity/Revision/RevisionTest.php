<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Revision;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Entity\Revision\Revision
 */
class RevisionTest extends AbstractTestCase
{
    /**
     * @covers ::setId
     * @covers ::getId
     * @covers ::getCommitHash
     * @covers ::setCommitHash
     * @covers ::getTitle
     * @covers ::setTitle
     * @covers ::getDescription
     * @covers ::setDescription
     * @covers ::getAuthorEmail
     * @covers ::setAuthorEmail
     * @covers ::getAuthorName
     * @covers ::setAuthorName
     * @covers ::getCreateTimestamp
     * @covers ::setCreateTimestamp
     * @covers ::getRepository
     * @covers ::setRepository
     * @covers ::getReview
     * @covers ::setReview
     */
    public function testAccessorPairs(): void
    {
        static::assertNull((new Revision())->getId());
        static::assertAccessorPairs(Revision::class);
    }
}
