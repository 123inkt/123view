<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Security\SessionKeys;
use DR\Review\Service\CodeReview\DiffComparePolicyProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[CoversClass(DiffComparePolicyProvider::class)]
class DiffComparePolicyProviderTest extends AbstractTestCase
{
    private RequestStack                $requestStack;
    private Request                     $request;
    private SessionInterface&MockObject $session;
    private DiffComparePolicyProvider   $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->requestStack = new RequestStack();
        $this->session      = $this->createMock(SessionInterface::class);
        $this->request      = new Request();
        $this->request->setSession($this->session);
        $this->provider = new DiffComparePolicyProvider($this->requestStack);
    }

    public function testDefault(): void
    {
        static::assertSame(DiffComparePolicy::ALL, $this->provider->getComparePolicy());
    }

    public function testGetComparePolicyWithoutSessionValue(): void
    {
        $this->requestStack->push($this->request);
        static::assertSame(DiffComparePolicy::ALL, $this->provider->getComparePolicy());
    }

    public function testGetComparePolicy(): void
    {
        $this->session->expects($this->once())
            ->method('get')
            ->with(SessionKeys::DIFF_COMPARISON_POLICY->value)
            ->willReturn(DiffComparePolicy::TRIM->value);

        $this->requestStack->push($this->request);
        static::assertSame(DiffComparePolicy::TRIM, $this->provider->getComparePolicy());
    }
}
