<?php
declare(strict_types=1);

namespace DR\Review\Tests\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Utils\Assert;

class RepositoryPropertyFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager): void
    {
        $repository = Assert::notNull($manager->getRepository(Repository::class)->findOneBy(['name' => 'repository']));

        $property = new RepositoryProperty('propertyKey', 'propertyValue');
        $property->setRepository($repository);
        $repository->getRepositoryProperties()->add($property);

        $manager->persist($property);
        $manager->persist($repository);
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
