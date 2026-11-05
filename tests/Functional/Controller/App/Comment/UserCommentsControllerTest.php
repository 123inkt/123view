<?php
declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\App\Comment;

use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Tests\DataFixtures\Test\UserCommentFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
class UserCommentsControllerTest extends AbstractFunctionalTestCase
{
    /**
     * @throws Exception
     */
    public function testUnauthenticatedAccessRedirects(): void
    {
        $this->client->request(Request::METHOD_GET, '/app/comments');

        self::assertResponseRedirects();
    }

    /**
     * @throws Exception
     */
    public function testShowCommentsAndReplies(): void
    {
        $this->login();
        $crawler = $this->client->request(Request::METHOD_GET, '/app/comments');

        self::assertResponseIsSuccessful();
        static::assertSame(
            [
                UserCommentFixtures::OTHER_COMMENT . ' ' . UserCommentFixtures::OWN_REPLY,
                UserCommentFixtures::OWN_DRAFT_COMMENT,
                UserCommentFixtures::OWN_DRAFT_COMMENT . ' ' . UserCommentFixtures::OWN_REPLY_ON_OWN_DRAFT,
                UserCommentFixtures::OWN_COMMENT,
            ],
            self::getMessages($crawler)
        );
        static::assertSame(
            ['displayName', 'displayName', 'displayName', 'displayName'],
            $crawler->filter('.list-group-item .badge.text-bg-primary')->each(static fn(Crawler $badge): string => $badge->text())
        );
        self::assertSelectorCount(4, '.card > .card-header');
        self::assertSelectorCount(4, '.card > .list-group-flush');
    }

    /**
     * @throws Exception
     */
    #[DataProvider('commentPagesProvider')]
    public function testCommentTabsAndMenu(string $url, string $activeTab): void
    {
        $this->login();
        $this->client->request(Request::METHOD_GET, $url);

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('h1', 'My comments and replies');
        self::assertSelectorCount(2, '.nav-tabs .nav-link');
        self::assertSelectorTextSame('.nav-tabs .nav-link.active[aria-current="page"]', $activeTab);
        self::assertSelectorExists('.nav-tabs a[href="/app/comments"]');
        self::assertSelectorTextSame('.nav-tabs a[href="/app/comments/drafts"]', 'Draft comments (1)');
        self::assertSelectorNotExists('header a[href="/app/comments/drafts"]');
        self::assertSelectorExists('header a[href="/app/comments"] .bi-chat-left-text');
        self::assertSelectorTextSame('header a[href="/app/comments"] .badge', '1');
    }

    /**
     * @return array<string, array{string, string}>
     */
    public static function commentPagesProvider(): array
    {
        return [
            'comments and replies' => ['/app/comments', 'Comments and replies'],
            'draft comments'       => ['/app/comments/drafts', 'Draft comments (1)'],
        ];
    }

    /**
     * @throws Exception
     */
    public function testTabsWithoutDraftComments(): void
    {
        $this->client->disableReboot();
        Assert::notNull($this->entityManager)->getConnection()->update(
            'comment',
            ['type' => CommentTypeEnum::Final->value],
            ['message' => UserCommentFixtures::OWN_DRAFT_COMMENT]
        );

        $this->login();
        $this->client->request(Request::METHOD_GET, '/app/comments');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('.nav-tabs a[href="/app/comments/drafts"]', 'Draft comments (0)');
        self::assertSelectorNotExists('header a[href="/app/comments"] .badge');

        $this->client->clickLink('Draft comments (0)');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('.nav-tabs .nav-link.active', 'Draft comments (0)');
        self::assertSelectorTextContains('.container-fluid.mt-3', 'You have no draft comments.');

        $this->client->clickLink('Comments and replies');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('.nav-tabs .nav-link.active', 'Comments and replies');
    }

    /**
     * @throws Exception
     */
    public function testSearchAndSortCommentsAndReplies(): void
    {
        $this->login();
        $crawler = $this->client->request(Request::METHOD_GET, '/app/comments?search=reply&order-by=update-timestamp');

        self::assertResponseIsSuccessful();
        static::assertSame(
            [
                UserCommentFixtures::OWN_DRAFT_COMMENT . ' ' . UserCommentFixtures::OWN_REPLY_ON_OWN_DRAFT,
                UserCommentFixtures::OTHER_COMMENT . ' ' . UserCommentFixtures::OWN_REPLY,
            ],
            self::getMessages($crawler)
        );
    }

    /**
     * @throws Exception
     */
    public function testInvalidOrderByIsRejected(): void
    {
        $this->login();
        $this->client->request(Request::METHOD_GET, '/app/comments?order-by=foobar');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [UserCommentFixtures::class];
    }

    /**
     * @throws Exception
     */
    private function login(): void
    {
        $this->client->loginUser(Assert::notNull(self::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com'])));
    }

    /**
     * @return string[]
     */
    private static function getMessages(Crawler $crawler): array
    {
        return $crawler->filter('.list-group-item')->each(
            static fn(Crawler $item): string => implode(
                ' ',
                $item->filter('.comment__markdown')->each(static fn(Crawler $node): string => trim($node->text()))
            )
        );
    }
}
