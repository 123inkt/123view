<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\Api\Report;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\Api\Report\UploadCodeCoverageController;
use DR\Review\Entity\Report\CodeCoverageFile;
use DR\Review\Entity\Report\CodeCoverageReport;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Report\CodeCoverageReportRepository;
use DR\Review\Request\Report\UploadCodeCoverageRequest;
use DR\Review\Service\Report\Coverage\CodeCoverageReportFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends AbstractControllerTestCase<UploadCodeCoverageController>
 */
#[CoversClass(UploadCodeCoverageController::class)]
class UploadCodeCoverageControllerTest extends AbstractControllerTestCase
{
    private RepositoryRepository&MockObject         $repositoryRepository;
    private CodeCoverageReportRepository&MockObject $reportRepository;
    private CodeCoverageReportFactory&MockObject    $reportFactory;

    protected function setUp(): void
    {
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->reportRepository     = $this->createMock(CodeCoverageReportRepository::class);
        $this->reportFactory        = $this->createMock(CodeCoverageReportFactory::class);
        parent::setUp();
    }

    public function testInvokeUnknownRepository(): void
    {
        $request = static::createStub(UploadCodeCoverageRequest::class);

        $this->repositoryRepository->expects($this->once())->method('findOneBy')->with(['name' => 'repository'])->willReturn(null);
        $this->reportRepository->expects($this->never())->method('save');
        $this->reportFactory->expects($this->never())->method('parse');

        $this->expectException(NotFoundHttpException::class);
        ($this->controller)($request, 'repository', 'hash');
    }

    public function testInvokeEmptyBody(): void
    {
        $request = static::createStub(UploadCodeCoverageRequest::class);
        $request->method('getData')->willReturn('');
        $repository = new Repository();

        $this->repositoryRepository->expects($this->once())->method('findOneBy')->with(['name' => 'repository'])->willReturn($repository);
        $this->reportRepository->expects($this->never())->method('save');
        $this->reportFactory->expects($this->never())->method('parse');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Body cannot be empty.');
        ($this->controller)($request, 'repository', 'hash');
    }

    public function testInvoke(): void
    {
        $request = static::createStub(UploadCodeCoverageRequest::class);
        $request->method('getFormat')->willReturn('format');
        $request->method('getBasePath')->willReturn('basePath');
        $request->method('getBranchId')->willReturn('branchId');
        $request->method('getData')->willReturn('data');
        $repository = new Repository();

        $report = new CodeCoverageReport();
        $report->getFiles()->add(new CodeCoverageFile());

        $this->repositoryRepository->expects($this->once())->method('findOneBy')->with(['name' => 'repository'])->willReturn($repository);
        $this->reportFactory->expects($this->once())
            ->method('parse')
            ->with($repository, 'hash', 'branchId', 'format', 'basePath', 'data')
            ->willReturn($report);
        $this->reportRepository->expects($this->once())->method('save')->with($report, true);

        $response = ($this->controller)($request, 'repository', 'hash');
        static::assertEquals(new JsonResponse(['created' => 1], Response::HTTP_OK), $response);
    }

    public function getController(): AbstractController
    {
        return new UploadCodeCoverageController($this->repositoryRepository, $this->reportRepository, $this->reportFactory);
    }
}
