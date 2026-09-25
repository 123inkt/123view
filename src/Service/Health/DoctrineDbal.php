<?php
declare(strict_types=1);

namespace DR\Review\Service\Health;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use DR\Utils\Assert;
use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Override;

/**
 * @codeCoverageIgnore
 */
class DoctrineDbal extends AbstractCheck
{
    public function __construct(private readonly ManagerRegistry $registry)
    {
    }

    /**
     * @throws Exception
     */
    #[Override]
    public function check(): ResultInterface
    {
        $connection = Assert::isInstanceOf($this->registry->getConnection(), Connection::class);
        $query      = $connection->getDatabasePlatform()->getDummySelectSQL();

        $connection->fetchOne($query);

        return new Success();
    }
}
