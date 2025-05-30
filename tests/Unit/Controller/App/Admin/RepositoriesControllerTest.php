<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Admin;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Admin\RepositoriesController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Admin\RepositoriesViewModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<RepositoriesController>
 */
#[CoversClass(RepositoriesController::class)]
class RepositoriesControllerTest extends AbstractControllerTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;

    protected function setUp(): void
    {
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $repository = new Repository();
        $repository->setId(123);

        $this->repositoryRepository->expects($this->once())->method('findBy')->with([], ['displayName' => 'ASC'])->willReturn([$repository]);

        $actual = ($this->controller)();
        static::assertEquals(['repositoriesViewModel' => new RepositoriesViewModel([$repository])], $actual);
    }

    public function getController(): AbstractController
    {
        return new RepositoriesController($this->repositoryRepository);
    }
}
