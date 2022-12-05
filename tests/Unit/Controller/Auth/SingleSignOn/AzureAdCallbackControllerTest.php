<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\Auth\SingleSignOn;

use DR\Review\Controller\Auth\SingleSignOn\AzureAdCallbackController;
use DR\Review\Tests\AbstractTestCase;
use RuntimeException;

/**
 * @coversDefaultClass \DR\Review\Controller\Auth\SingleSignOn\AzureAdCallbackController
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
