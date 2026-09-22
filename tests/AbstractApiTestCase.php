<?php

declare(strict_types=1);

namespace DR\Review\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase as BaseApiTestCase;
use ApiPlatform\Symfony\Bundle\Test\Client;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use DR\Utils\Assert;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Throwable;

abstract class AbstractApiTestCase extends BaseApiTestCase
{
    protected ?AbstractDatabaseTool   $databaseTool;
    protected ?EntityManagerInterface $entityManager;
    protected Client                  $client;

    /**
     * @see https://latteandcode.medium.com/symfony-improving-your-tests-with-doctrinefixturesbundle-1a37b704ac05
     * @throws Throwable
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->client        = static::createClient(
            ['environment' => 'test', 'debug' => 'false'],
            ['headers' => ['accept' => ['application/json']]],
        );
        $this->databaseTool  = Assert::isInstanceOf(static::getContainer()->get(DatabaseToolCollection::class), DatabaseToolCollection::class)->get();
        $doctrine            = Assert::isInstanceOf(static::getContainer()->get('doctrine'), ManagerRegistry::class);
        $this->entityManager = Assert::isInstanceOf($doctrine->getManager(), EntityManagerInterface::class);

        Assert::isInstanceOf($doctrine->getConnection(), Connection::class)->beginTransaction();

        $fixtures = $this->getFixtures();
        if (count($fixtures) > 0) {
            $this->databaseTool->loadFixtures($fixtures);
        }
    }

    /**
     * @throws Throwable
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
     * @param class-string<T> $serviceId
     *
     * @return T
     * @throws Throwable
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
