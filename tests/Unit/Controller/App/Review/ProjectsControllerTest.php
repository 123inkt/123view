<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\ProjectsController;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Repository\Config\RepositoryRepository;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\ProjectsController
 * @covers ::__construct
 */
class ProjectsControllerTest extends AbstractControllerTestCase
{
    private RepositoryRepository&MockObject $repositoryRepository;
    private TranslatorInterface&MockObject  $translator;

    public function setUp(): void
    {
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->translator           = $this->createMock(TranslatorInterface::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $repositoryA = new Repository();
        $repositoryB = new Repository();

        $this->repositoryRepository->expects(self::exactly(2))
            ->method('findBy')
            ->withConsecutive(
                [['active' => 1, 'favorite' => 1], ['name' => 'ASC']],
                [['active' => 1, 'favorite' => 0], ['name' => 'ASC']]
            )
            ->willReturn([$repositoryA], [$repositoryB]);
        $this->translator->expects(self::once())->method('trans')->with('projects')->willReturn('Projects');

        $result = ($this->controller)();
        static::assertSame('Projects', $result['page_title']);
    }

    public function getController(): AbstractController
    {
        return new ProjectsController($this->repositoryRepository, $this->translator);
    }
}
