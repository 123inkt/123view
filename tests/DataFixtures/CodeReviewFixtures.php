<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Utils\Assert;

class CodeReviewFixtures extends Fixture implements DependentFixtureInterface
{
    public const REVIEW_ID  = 1504;
    public const PROJECT_ID = 7327;

    public function load(ObjectManager $manager): void
    {
        /** @var ClassMetadata<CodeReview> $metadata */
        $metadata = $manager->getClassMetadata(CodeReview::class);
        $metadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_NONE);

        $repository = Assert::notNull($manager->getRepository(Repository::class)->findOneBy(['name' => 'repository']));

        $review = new CodeReview();
        $review->setId(self::REVIEW_ID);
        $review->setProjectId(self::PROJECT_ID);
        $review->setState(CodeReviewStateType::CLOSED);
        $review->setTitle('title');
        $review->setReferenceId('reference');
        $review->setDescription('description');
        $review->setCreateTimestamp(12346789);
        $review->setUpdateTimestamp(12346789);
        $review->setRepository($repository);
        $manager->persist($review);
        $manager->flush();
    }

    /**
     * @inheritDoc
     */
    public function getDependencies(): array
    {
        return [RepositoryFixtures::class];
    }
}
