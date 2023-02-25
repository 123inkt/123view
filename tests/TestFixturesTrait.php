<?php
declare(strict_types=1);

namespace DR\Review\Tests;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use DR\Review\Utility\Assert;
use Exception;
use Liip\TestFixturesBundle\Services\DatabaseToolCollection;
use Liip\TestFixturesBundle\Services\DatabaseTools\AbstractDatabaseTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

trait TestFixturesTrait
{
    protected ?AbstractDatabaseTool   $databaseTool;
    protected ?EntityManagerInterface $entityManager;
    protected KernelBrowser           $client;

    /**
     * @see https://latteandcode.medium.com/symfony-improving-your-tests-with-doctrinefixturesbundle-1a37b704ac05
     * @throws Exception
     */
    private function setupFixtures(): void
    {
        $this->client        = static::createClient(['environment' => 'test', 'debug' => 'false']);
        $this->databaseTool  = self::getService(DatabaseToolCollection::class)->get();
        $doctrine            = self::getService(Registry::class, 'doctrine');
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
    private function teardownFixtures(): void
    {
        // this call will shutdown the kernel and close any open connections. Ensuring the rollback of any transactions.
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
