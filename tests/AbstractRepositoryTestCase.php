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
use Symfony\Component\ErrorHandler\ErrorHandler;

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
        $this->restoreExceptionHandler();
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
     * @return class-string[]
     */
    abstract protected function getFixtures(): array;

    /**
     * After the kernel completes, remove all exception handlers
     * @link https://github.com/symfony/symfony/issues/53812#issuecomment-1958859357
     */
    private function restoreExceptionHandler(): void
    {
        $res = [];

        while (true) {
            $previousHandler = set_exception_handler(static fn() => null);
            restore_exception_handler();

            var_dump($previousHandler);

            if (is_array($previousHandler) && $previousHandler[0] instanceof ErrorHandler && $previousHandler[1] === 'handleException') {
                restore_exception_handler();
                continue;
            }

            if ($previousHandler === null) {
                break;
            }

            $res[] = $previousHandler;
            restore_exception_handler();
        }

        $res = array_reverse($res);
        foreach ($res as $handler) {
            set_exception_handler($handler);
        }
    }
}
