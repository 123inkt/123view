<?php
declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\App\Comment;

use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Tests\DataFixtures\UserCommentFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversNothing;
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
