<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Util;

use DR\Review\Service\Util\SystemLoadService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SystemLoadService::class)]
class SystemLoadServiceTest extends AbstractTestCase
{
    private SystemLoadService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SystemLoadService();
    }

    public function testGetLoad(): void
    {
        static::assertGreaterThanOrEqual(0, $this->service->getLoad());
    }
}
