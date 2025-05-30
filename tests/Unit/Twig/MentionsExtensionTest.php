<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Entity\User\User;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\MentionsExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(MentionsExtension::class)]
class MentionsExtensionTest extends AbstractTestCase
{
    private CommentMentionService&MockObject $mentionService;
    private MentionsExtension                $extension;

    public function setUp(): void
    {
        parent::setUp();
        $this->mentionService = $this->createMock(CommentMentionService::class);
        $this->extension      = new MentionsExtension($this->mentionService);
    }

    public function testGetFilters(): void
    {
        static::assertCount(1, $this->extension->getFilters());
    }

    public function testConvert(): void
    {
        $user = new User();
        $user->setId(123);
        $user->setName('Sherlock Holmes');
        $user->setEmail('sherlock@example.com');

        $this->mentionService->expects($this->once())->method('getMentionedUsers')->willReturn(['@user:123[Frank Dekker]' => $user]);
        $this->mentionService->expects($this->once())->method('replaceMentionedUsers')
            ->with('message', ['@user:123[Frank Dekker]' => $user])
            ->willReturn('message');

        $actual = $this->extension->convert('message');
        static::assertSame('message', $actual);
    }
}
