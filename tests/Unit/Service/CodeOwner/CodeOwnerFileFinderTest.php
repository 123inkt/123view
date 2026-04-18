<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeOwner;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\CodeOwner\CodeOwnerFileFinder;
use DR\Review\Service\Git\GitRepositoryLocationService;
use DR\Review\Tests\AbstractTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeOwnerFileFinder::class)]
class CodeOwnerFileFinderTest extends AbstractTestCase
{
    private CodeOwnerFileFinder               $finder;
    private vfsStreamDirectory                $root;
    private Repository                        $repository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->root       = vfsStream::setup('repo');
        $locationService  = $this->createStub(GitRepositoryLocationService::class);
        $locationService->method('getLocation')->willReturn($this->root->url());

        $this->finder     = new CodeOwnerFileFinder($locationService);
        $this->repository = new Repository();
    }

    public function testFindWithCodeOwnersInRoot(): void
    {
        vfsStream::newFile('CODEOWNERS')->at($this->root);
        vfsStream::newDirectory('src/Controller')->at($this->root);

        $result = $this->finder->find($this->repository, 'src/Controller/UserController.php');

        static::assertSame([$this->root->url() . '/CODEOWNERS'], $result);
    }

    public function testFindWithCodeOwnersInSubdirectory(): void
    {
        vfsStream::newDirectory('src/Controller')->at($this->root);
        vfsStream::newFile('CODEOWNERS')->at($this->root->getChild('src/Controller'));

        $result = $this->finder->find($this->repository, 'src/Controller/UserController.php');

        static::assertSame([$this->root->url() . '/src/Controller/CODEOWNERS'], $result);
    }

    public function testFindWithCodeOwnersInBothRootAndSubdirectory(): void
    {
        vfsStream::newFile('CODEOWNERS')->at($this->root);
        vfsStream::newDirectory('src/Controller')->at($this->root);
        vfsStream::newFile('CODEOWNERS')->at($this->root->getChild('src/Controller'));

        $result = $this->finder->find($this->repository, 'src/Controller/UserController.php');

        static::assertSame(
            [
                $this->root->url() . '/src/Controller/CODEOWNERS',
                $this->root->url() . '/CODEOWNERS',
            ],
            $result
        );
    }

    public function testFindReturnsEmptyArrayWhenNoCodeOwnersExist(): void
    {
        $result = $this->finder->find($this->repository, 'src/Controller/UserController.php');

        static::assertSame([], $result);
    }
}
