<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Search\RipGrep;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResult;
use DR\Review\Service\Git\GitRepositoryLocationService;
use DR\Review\Service\Search\RipGrep\SearchResultFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Finder\SplFileInfo;

#[CoversClass(SearchResultFactory::class)]
class SearchResultFactoryTest extends AbstractTestCase
{
    private GitRepositoryLocationService&MockObject $locationService;
    private SearchResultFactory                     $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->locationService = $this->createMock(GitRepositoryLocationService::class);
        $this->factory         = new SearchResultFactory($this->locationService);
    }

    public function testCreateWithMatchingRepository(): void
    {
        $repository = new Repository();

        $this->locationService->expects($this->once())->method('getLocation')->with($repository)->willReturn('/basepath/repositoryPath/');

        $expected = new SearchResult(
            $repository,
            new SplFileInfo('/basepath/repositoryPath/directory/file.json', '/basepath/repositoryPath', 'directory/file.json')
        );
        $result   = $this->factory->create('repositoryPath/directory/file.json', '/basepath/', [$repository]);
        static::assertEquals($expected, $result);
    }

    public function testCreateWithoutMatchingRepository(): void
    {
        $repository = new Repository();

        $this->locationService->expects($this->once())->method('getLocation')->with($repository)->willReturn('foobar');

        $result   = $this->factory->create('repositoryPath/directory/file.json', '/basepath/', [$repository]);
        static::assertNull($result);
    }
}
