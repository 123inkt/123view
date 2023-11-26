<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Revision;

use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewRevisionAdded::class)]
class ReviewRevisionAddedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(
            new ReviewRevisionAdded(5, 6, 7, 'title'),
            'review-revision-added',
            5,
            ['reviewId' => 5, 'revisionId' => 6, 'userId' => 7, 'title' => 'title']
        );
        static::assertUserAware(new ReviewRevisionAdded(5, 6, 7, 'title'), 7);
    }
}
