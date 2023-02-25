<?php

declare(strict_types=1);

namespace DR\Review\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use DR\Review\Utility\Assert;
use Exception;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

abstract class AbstractFunctionalTestCase extends WebTestCase
{
    protected ?AbstractDatabaseTool   $databaseTool;
    protected ?EntityManagerInterface $entityManager;
    protected KernelBrowser           $client;

    /**
     * @see https://latteandcode.medium.com/symfony-improving-your-tests-with-doctrinefixturesbundle-1a37b704ac05
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client        = static::createClient(['environment' => 'test', 'debug' => 'false']);
        $this->databaseTool  = Assert::instanceOf(DatabaseToolCollection::class, static::getContainer()->get(DatabaseToolCollection::class))->get();
        $doctrine            = Assert::instanceOf(ManagerRegistry::class, static::getContainer()->get('doctrine'));
        $this->entityManager = Assert::instanceOf(EntityManagerInterface::class, $doctrine->getManager());

        Assert::instanceOf(Connection::class, $doctrine->getConnection())->beginTransaction();

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
        $this->entityManager?->close();
        $this->entityManager = null;
    }

    /**
     * @template T of object
     *
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
     * @return class-string[]
     */
    abstract protected function getFixtures(): array;
}
