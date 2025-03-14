<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Revision;

use DR\Review\Service\Revision\RevisionTitleNormalizer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RevisionTitleNormalizer::class)]
class RevisionTitleNormalizerTest extends AbstractTestCase
{
    public function testNormalize(): void
    {
        $normalizer = new RevisionTitleNormalizer();
        static::assertSame('F#123 US#456 My task', $normalizer->normalize('F#123 US#456 My task'));
        static::assertSame('F#123 US#456 My task', $normalizer->normalize('   F#123 US#456 My task'));
        static::assertSame('F#123 US#456 My task', $normalizer->normalize('Revert "F#123 US#456 My task"'));
        static::assertSame('F#123 US#456 My task', $normalizer->normalize('Reapply "F#123 US#456 My task"'));
    }
}
