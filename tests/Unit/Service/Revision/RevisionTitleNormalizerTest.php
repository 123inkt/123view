<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Revision;

use DR\GitCommitNotification\Service\Revision\RevisionTitleNormalizer;
use DR\GitCommitNotification\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Revision\RevisionTitleNormalizer
 */
class RevisionTitleNormalizerTest extends AbstractTestCase
{
    /**
     * @covers ::normalize
     */
    public function testNormalize(): void
    {
        $normalizer = new RevisionTitleNormalizer();
        static::assertSame('F#123 US#456 My task', $normalizer->normalize('F#123 US#456 My task'));
        static::assertSame('F#123 US#456 My task', $normalizer->normalize('   F#123 US#456 My task'));
        static::assertSame('F#123 US#456 My task', $normalizer->normalize('Revert "F#123 US#456 My task"'));
    }
}
