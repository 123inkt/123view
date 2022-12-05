<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Revision;

use DR\Review\Service\Revision\RevisionTitleNormalizer;
use DR\Review\Tests\AbstractTestCase;

/**
 * @coversDefaultClass \DR\Review\Service\Revision\RevisionTitleNormalizer
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
