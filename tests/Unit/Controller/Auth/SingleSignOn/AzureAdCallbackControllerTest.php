<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\Auth\SingleSignOn;

use DR\GitCommitNotification\Controller\Auth\SingleSignOn\AzureAdCallbackController;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use RuntimeException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\Auth\SingleSignOn\AzureAdCallbackController
 * @covers ::__construct
 */
class AzureAdCallbackControllerTest extends AbstractTestCase
{
    /**
     * @covers ::__invoke
     */
    public function tesInvoke(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('AzureAdAuthenticator route is not configured');
        (new AzureAdCallbackController())();
    }
}
