<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Repository\Review;

use DR\Review\Entity\Review\UserMention;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\Review\UserMentionRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CommentFixtures;
use DR\Review\Tests\DataFixtures\UserFixtures;
use DR\Review\Utility\Assert;
use Exception;

/**
 * @coversDefaultClass \DR\Review\Repository\Review\UserMentionRepository
 * @covers ::__construct
 */
class UserMentionRepositoryTest extends AbstractRepositoryTestCase
{
    /**
     * @covers ::saveAll
     * @throws Exception
     */
    public function testSaveAll(): void
    {
        $user    = Assert::notNull(static::getService(UserRepository::class)->findOneBy(['email' => 'sherlock@example.com']));
        $comment = Assert::notNull(static::getService(CommentRepository::class)->findOneBy(['message' => 'message']));

        $mention = new UserMention();
        $mention->setUserId((int)$user->getId());
        $mention->setComment($comment);

        $mentionRepository = static::getService(UserMentionRepository::class);
        $mentionRepository->saveAll($comment, [$mention]);

        static::assertCount(1, $mentionRepository->findAll());

        $comment->getMentions()->add($mention);
        $mentionRepository->saveAll($comment, []);
        static::assertCount(0, $mentionRepository->findAll());
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [UserFixtures::class, CommentFixtures::class];
    }
}
