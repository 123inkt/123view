<?php

declare(strict_types=1);

namespace DR\Review\Tests\Integration\Entity\Review;

use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Tests\AbstractRepositoryTestCase;
use DR\Review\Tests\DataFixtures\CommentFixtures;
use DR\Utils\Assert;
use Exception;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class CommentStatePersistenceTest extends AbstractRepositoryTestCase
{
    /**
     * @throws Exception
     */
    public function testResolvedStateHydratesAsEnum(): void
    {
        $repository = static::getService(CommentRepository::class);
        $comment    = Assert::notNull($repository->findOneBy(['message' => 'message']));
        $commentId  = $comment->getId();

        $repository->save($comment->setState(CommentStateEnum::Resolved), true);
        Assert::notNull($this->entityManager)->clear();

        $hydratedComment = Assert::notNull($repository->find($commentId));
        static::assertSame(CommentStateEnum::Resolved, $hydratedComment->getState());
    }

    /**
     * @return list<class-string>
     */
    protected function getFixtures(): array
    {
        return [CommentFixtures::class];
    }
}
