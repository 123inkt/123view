<?php
declare(strict_types=1);

namespace DR\Review\Service\Health;

use Doctrine\DBAL\Platforms\AbstractPlatform;
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

    #[Override]
    public function check(): ResultInterface
    {
        $connection = $this->registry->getConnection();
        $query      = Assert::isInstanceOf($connection->getDatabasePlatform(), AbstractPlatform::class)->getDummySelectSQL();

        $connection->fetchOne($query);

        return new Success();
    }
}
