<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Checkout;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Service\Git\Checkout\GitCheckoutService;
use DR\Review\Service\Git\Checkout\RecoverableGitCheckoutService;
use DR\Review\Service\Git\Reset\GitResetService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Process\Exception\ProcessFailedException;

#[CoversClass(RecoverableGitCheckoutService::class)]
class RecoverableGitCheckoutServiceTest extends AbstractTestCase
{
    private GitCheckoutService&MockObject $checkoutService;
    private GitResetService&MockObject    $resetService;
    private RecoverableGitCheckoutService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checkoutService = $this->createMock(GitCheckoutService::class);
        $this->resetService    = $this->createMock(GitResetService::class);
        $this->service         = new RecoverableGitCheckoutService($this->checkoutService, $this->resetService);
    }

    /**
     * @throws RepositoryException
     */
    public function testCheckoutRevisionWithoutFailure(): void
    {
        $revision = new Revision();

        $this->checkoutService->expects($this->once())->method('checkoutRevision')->with($revision)->willReturn('success');
        $this->resetService->expects($this->never())->method('resetHard');

        static::assertSame('success', $this->service->checkoutRevision($revision));
    }

    /**
     * @throws RepositoryException
     */
    public function testCheckoutRevisionWithProcessFailure(): void
    {
        $repository = new Repository();
        $revision   = (new Revision())->setRepository($repository);

        $processException = $this->createMock(ProcessFailedException::class);

        $this->checkoutService->expects($this->exactly(2))
            ->method('checkoutRevision')
            ->with($revision)
            ->willReturnOnConsecutiveCalls($this->throwException($processException), 'success');
        $this->resetService->expects($this->once())->method('resetHard')->with($repository);

        static::assertSame('success', $this->service->checkoutRevision($revision));
    }
}
