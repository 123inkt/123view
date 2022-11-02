<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Twig;

use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Service\CodeReview\Comment\CommentMentionService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use DR\GitCommitNotification\Twig\MentionsExtension;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Twig\MentionsExtension
 * @covers ::__construct
 */
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

    /**
     * @covers ::getFilters
     */
    public function testGetFilters(): void
    {
        static::assertCount(1, $this->extension->getFilters());
    }

    /**
     * @covers ::convert
     */
    public function testConvert(): void
    {
        $user = new User();
        $user->setId(123);
        $user->setName('Sherlock Holmes');
        $user->setEmail('sherlock@example.com');

        $this->mentionService->expects(self::once())->method('getMentionedUsers')->willReturn(['@user:123[Frank Dekker]' => $user]);

        $actual = $this->extension->convert('foobar @user:123[Frank Dekker] foobar @user:456[unknown] foobar');
        static::assertSame('foobar [@Sherlock Holmes](mailto:sherlock@example.com) foobar @user:456[unknown] foobar', $actual);
    }
}
