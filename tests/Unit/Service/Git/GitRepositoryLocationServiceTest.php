<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Git;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\Git\GitRepositoryLocationService;
use DR\Review\Tests\AbstractTestCase;
use League\Uri\Uri;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(GitRepositoryLocationService::class)]
class GitRepositoryLocationServiceTest extends AbstractTestCase
{
    private GitRepositoryLocationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new GitRepositoryLocationService('/cache/');
    }

    public function testGetLocation(): void
    {
        $repository = new Repository();
        $repository->setUrl(Uri::new('https://github.com/123inkt/123view.git'));

        $result = $this->service->getLocation($repository);
        static::assertSame('/cache/123view-5a9cd4cb427f55e41193e601feacfcdfdcfdb3fe/', $result);
    }
}
