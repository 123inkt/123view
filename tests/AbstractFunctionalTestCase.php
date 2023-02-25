<?php

declare(strict_types=1);

namespace DR\Review\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\User\User;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractFunctionalTestCase extends WebTestCase
{
    /** Are the fixtures loaded within database transaction */
    protected const REQUIRE_TRANSACTION = true;

    protected ?AbstractDatabaseTool $databaseTool;
    protected ?ObjectManager        $entityManager;
    protected KernelBrowser         $client;

    protected function getUser(int $userId): User
    {
        /** @var User|null $user */
        $user = $this->entityManager->getRepository(User::class)->find($userId);
        static::assertNotNull($user);

        return $user;
    }

    /**
     * @see https://latteandcode.medium.com/symfony-improving-your-tests-with-doctrinefixturesbundle-1a37b704ac05
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client        = static::createClient(['environment' => 'test', 'debug' => 'false']);
        $this->databaseTool  = static::getContainer()->get(DatabaseToolCollection::class)->get();
        $doctrine            = static::getContainer()->get('doctrine');
        $this->entityManager = $doctrine->getManager();

        if (self::REQUIRE_TRANSACTION) {
            /** @var Connection $connection */
            $connection = $doctrine->getConnection();
            $connection->beginTransaction();
        }

        $fixtures = $this->getFixtures();
        if (count($fixtures) > 0) {
            $this->databaseTool->loadFixtures($fixtures);
        }
    }

    /**
     * @throws Exception
     */
    protected function tearDown(): void
    {
        // this call will shutdown the kernel and close any open connections. Ensuring the rollback of any transactions.
        parent::tearDown();
        $this->databaseTool = null;
        $this->entityManager->close();
        $this->entityManager = null;
    }

    /**
     * @template T of EntityRepository
     *
     * @param class-string<T> $repositoryClassName
     *
     * @return EntityRepository<T>
     * @throws \Exception
     */
    protected function getRepository(string $repositoryClassName): object
    {
        /** @var EntityRepository<T> $repository */
        $repository = self::getContainer()->get(ManagerRegistry::class)->getManager()->getRepository($repositoryClassName);

        return $repository;
    }

    /**
     * @return class-string[]
     */
    abstract protected function getFixtures(): array;
}
