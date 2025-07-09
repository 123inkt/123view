<?php
declare(strict_types=1);

namespace DR\Review\Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManager;
use Exception;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class AbstractRepositoryTestCase extends KernelTestCase
{
    protected ?AbstractDatabaseTool $databaseTool;
    protected ?EntityManager        $entityManager;
    protected KernelBrowser         $client;

    /**
     * @see https://latteandcode.medium.com/symfony-improving-your-tests-with-doctrinefixturesbundle-1a37b704ac05
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel(['environment' => 'test', 'debug' => 'false']);
        $this->databaseTool = self::getService(DatabaseToolCollection::class)->get();
        $doctrine           = self::getService(Registry::class, 'doctrine');
        $entityManager      = $doctrine->getManager();
        assert($entityManager instanceof EntityManager);
        $this->entityManager = $entityManager;

        /** @var Connection $connection */
        $connection = $doctrine->getConnection();
        $connection->beginTransaction();

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
        if ($this->entityManager !== null) {
            $this->entityManager->close();
        }
        $this->entityManager = null;
    }

    /**
     * @template T of object
     * @param class-string<T> $serviceId
     *
     * @return T
     * @throws Exception
     */
    protected static function getService(string $serviceId, ?string $alias = null): object
    {
        /** @var T $service */
        $service = self::getContainer()->get($alias ?? $serviceId);

        return $service;
    }

    /**
     * @return list<class-string>
     */
    abstract protected function getFixtures(): array;
}
