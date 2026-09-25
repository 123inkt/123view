<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\Api\Report;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\Api\Report\UploadCodeInspectionController;
use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Entity\Report\CodeInspectionReport;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\Request\Report\UploadCodeInspectionRequest;
use DR\Review\Service\Report\CodeInspection\CodeInspectionReportFactory;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends AbstractControllerTestCase<UploadCodeInspectionController>
 */
#[CoversClass(UploadCodeInspectionController::class)]
class UploadCodeInspectionControllerTest extends AbstractControllerTestCase
{
    private RepositoryRepository&MockObject           $repositoryRepository;
    private CodeInspectionReportRepository&MockObject $reportRepository;
    private CodeInspectionReportFactory&MockObject    $reportFactory;

    protected function setUp(): void
    {
        $this->repositoryRepository = $this->createMock(RepositoryRepository::class);
        $this->reportRepository     = $this->createMock(CodeInspectionReportRepository::class);
        $this->reportFactory        = $this->createMock(CodeInspectionReportFactory::class);
        parent::setUp();
    }

    public function testInvokeUnknownRepository(): void
    {
        $request = static::createStub(UploadCodeInspectionRequest::class);

        $this->repositoryRepository->expects($this->once())->method('findOneBy')->with(['name' => 'repository'])->willReturn(null);
        $this->reportRepository->expects($this->never())->method('removeOneBy');
        $this->reportFactory->expects($this->never())->method('parse');

        $this->expectException(NotFoundHttpException::class);
        ($this->controller)($request, 'repository', 'hash');
    }

    public function testInvokeEmptyBody(): void
    {
        $request = static::createStub(UploadCodeInspectionRequest::class);
        $request->method('getData')->willReturn('');
        $repository = new Repository();

        $this->repositoryRepository->expects($this->once())->method('findOneBy')->with(['name' => 'repository'])->willReturn($repository);
        $this->reportRepository->expects($this->never())->method('removeOneBy');
        $this->reportFactory->expects($this->never())->method('parse');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Body cannot be empty.');
        ($this->controller)($request, 'repository', 'hash');
    }

    public function testInvoke(): void
    {
        $request = static::createStub(UploadCodeInspectionRequest::class);
        $request->method('getIdentifier')->willReturn('identifier');
        $request->method('getBranchId')->willReturn('branchId');
        $request->method('getFormat')->willReturn('format');
        $request->method('getBasePath')->willReturn('basePath');
        $request->method('getSubDirectory')->willReturn('subDirectory');
        $request->method('getData')->willReturn('data');
        $repository = new Repository();

        $report = new CodeInspectionReport();
        $report->getIssues()->add(new CodeInspectionIssue());

        $this->repositoryRepository->expects($this->once())->method('findOneBy')->with(['name' => 'repository'])->willReturn($repository);
        $this->reportFactory->expects($this->once())
            ->method('parse')
            ->with($repository, 'hash', 'identifier', 'branchId', 'format', 'basePath', 'subDirectory', 'data')
            ->willReturn($report);
        $this->reportRepository->expects($this->once())
            ->method('removeOneBy')
            ->with(['repository' => $repository, 'inspectionId' => 'identifier', 'commitHash' => 'hash']);
        $this->reportRepository->expects($this->once())->method('save')->with($report, true);

        $response = ($this->controller)($request, 'repository', 'hash');
        static::assertEquals(new JsonResponse(['created' => 1], Response::HTTP_OK), $response);
    }

    public function getController(): AbstractController
    {
        return new UploadCodeInspectionController($this->repositoryRepository, $this->reportRepository, $this->reportFactory);
    }
}
