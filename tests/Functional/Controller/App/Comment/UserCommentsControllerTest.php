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
        self::assertSelectorCount(4, '.list-group-item > a[href*="#focus:"]');
        self::assertSelectorCount(4, '.card > .card-header');
        self::assertSelectorCount(4, '.card > .list-group-flush');
    }

    /**
     * @throws Exception
     */
    public function testCommentsAndRepliesUseSharedCommentRendering(): void
    {
        $this->login();
        $this->client->request(Request::METHOD_GET, '/app/comments');

        self::assertResponseIsSuccessful();
        self::assertSelectorCount(4, '.comment__comment-thread');
        self::assertSelectorCount(4, '.comment__comment > .comment__author_and_time');
        self::assertSelectorCount(2, '.comment__reply > .comment__author_and_time');
        self::assertSelectorCount(6, '.comment__message.comment__markdown');
        self::assertSelectorCount(2, '.comment__draft');
        self::assertSelectorCount(6, 'a[title="Copy the link to the comment to clipboard"]');
        self::assertSelectorNotExists('.comment__edit, .comment__toolbar');
    }

    /**
     * @throws Exception
     */
    public function testCommentMarkdown(): void
    {
        $message = <<<'MARKDOWN'
            ## Review heading

            **Bold text** and `inline code`

            First line<br>Second line

            - First item
            - Second item

            ```php
            echo 'example';
            ```
            MARKDOWN;

        Assert::notNull($this->entityManager)->getConnection()->update(
            'comment',
            ['message' => $message],
            ['message' => UserCommentFixtures::OWN_COMMENT]
        );

        $this->login();
        $this->client->request(Request::METHOD_GET, '/app/comments');

        self::assertResponseIsSuccessful();
        self::assertSelectorTextSame('.comment__message h2', 'Review heading');
        self::assertSelectorTextSame('.comment__message strong', 'Bold text');
        self::assertSelectorTextSame('.comment__message p code', 'inline code');
        self::assertSelectorExists('.comment__message p br');
        self::assertSelectorCount(2, '.comment__message ul li');
        self::assertSelectorTextContains('.comment__message pre', "echo 'example';");
    }

    /**
     * @throws Exception
     */
    public function testDraftCommentRenderingMatchesBothTabs(): void
    {
        $this->client->disableReboot();
        Assert::notNull($this->entityManager)->getConnection()->update(
            'comment',
            ['message' => '**Draft text**<br>Next line with `code`'],
            ['message' => UserCommentFixtures::OWN_DRAFT_COMMENT]
        );

        $this->login();
        $comments = $this->client->request(Request::METHOD_GET, '/app/comments');
        self::assertResponseIsSuccessful();
        $commentHtml = $comments->filter('.comment__draft .comment__comment .comment__message')->first()->html();

        $drafts = $this->client->request(Request::METHOD_GET, '/app/comments/drafts');
        self::assertResponseIsSuccessful();

        static::assertSame($commentHtml, $drafts->filter('.comment__draft .comment__comment .comment__message')->first()->html());
        self::assertSelectorExists('.comment__draft[data-controller~="comment-thread"] .comment__edit');
        self::assertSelectorCount(1, '.comment__draft .comment__reply');
    }

    /**
     * @throws Exception
     */
    #[DataProvider('commentPagesProvider')]
    public function testCommentTabsAndMenu(string $url, string $activeTab, string $pageTitle): void
    {
        $this->login();
        $this->client->request(Request::METHOD_GET, $url);

        self::assertResponseIsSuccessful();
        self::assertPageTitleContains($pageTitle);
        self::assertSelectorTextSame('h1', 'My comments and replies');
        self::assertSelectorCount(2, '.nav-tabs .nav-link');
        self::assertSelectorTextSame('.nav-tabs .nav-link.active[aria-current="page"]', $activeTab);
        self::assertSelectorExists('.nav-tabs a[href="/app/comments"]');
        self::assertSelectorTextSame('.nav-tabs a[href="/app/comments/drafts"]', 'Draft comments (1)');
        self::assertSelectorNotExists('header a[href="/app/comments/drafts"]');
        self::assertSelectorTextContains('header a[href="/app/comments"]', 'Comments');
        self::assertSelectorTextSame('header a[href="/app/comments"] .badge', '1');
    }

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function commentPagesProvider(): array
    {
        return [
            'comments and replies' => ['/app/comments', 'Comments and replies', 'My comments and replies'],
            'draft comments'       => ['/app/comments/drafts', 'Draft comments (1)', 'Draft comments'],
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
