<?php

declare(strict_types=1);

namespace DR\Review\Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use DR\Utils\Assert;
use Exception;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

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

    protected function getResponseContent(): string
    {
        return Assert::notFalse(Assert::isInstanceOf($this->client->getResponse(), Response::class)->getContent());
    }

    /**
     * @return array<array-key, mixed>
     * @throws JsonException
     */
    protected function getResponseArray(): array
    {
        return Assert::isArray(Json::decode($this->getResponseContent(), true));
    }

    /**
     * @return list<class-string>
     */
    abstract protected function getFixtures(): array;
}
