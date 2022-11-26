<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\Auth\SingleSignOn;

use DR\GitCommitNotification\Controller\Auth\SingleSignOn\AzureAdCallbackController;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use RuntimeException;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\Auth\SingleSignOn\AzureAdCallbackController
 */
class AzureAdCallbackControllerTest extends AbstractTestCase
{
    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('AzureAdAuthenticator route is not configured');
        (new AzureAdCallbackController())();
    }
}
