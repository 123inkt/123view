<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git\Review;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Service\Git\Review\ReviewSessionService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[CoversClass(ReviewSessionService::class)]
class ReviewSessionServiceTest extends AbstractTestCase
{
    private RequestStack         $requestStack;
    private ReviewSessionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestStack = new RequestStack();
        $this->service      = new ReviewSessionService($this->requestStack);
    }

    public function testGetDiffComparePolicyForUserWithoutRequest(): void
    {
        static::assertSame(DiffComparePolicy::ALL, $this->service->getDiffComparePolicyForUser());
    }

    public function testGetDiffComparePolicyForUserWithEmptySession(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('get')->with('diff-comparison-policy')->willReturn(null);

        $request = new Request();
        $request->setSession($session);

        $this->requestStack->push($request);

        static::assertSame(DiffComparePolicy::ALL, $this->service->getDiffComparePolicyForUser());
    }

    public function testGetDiffComparePolicyForUser(): void
    {
        $session = $this->createMock(SessionInterface::class);
        $session->expects($this->once())->method('get')->with('diff-comparison-policy')->willReturn('trim');

        $request = new Request();
        $request->setSession($session);

        $this->requestStack->push($request);

        static::assertSame(DiffComparePolicy::TRIM, $this->service->getDiffComparePolicyForUser());
    }
}
