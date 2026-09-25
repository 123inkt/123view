<?php
declare(strict_types=1);

namespace DR\Review\Controller\Api\Report;

use DR\Review\Controller\AbstractController;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Report\CodeCoverageReportRepository;
use DR\Review\Request\Report\UploadCodeCoverageRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Report\Coverage\CodeCoverageReportFactory;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UploadCodeCoverageController extends AbstractController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly CodeCoverageReportRepository $reportRepository,
        private readonly CodeCoverageReportFactory $reportFactory
    ) {
    }

    #[Route('/api/report/code-coverage/{repositoryName<[a-z0-9-]+>}/{commitHash<[a-zA-Z0-9]{6,255}>}', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(UploadCodeCoverageRequest $request, string $repositoryName, string $commitHash): Response
    {
        $repository = $this->repositoryRepository->findOneBy(['name' => $repositoryName]);
        if ($repository === null) {
            throw new NotFoundHttpException();
        }
        if (strlen(trim($request->getData())) === 0) {
            throw new BadRequestHttpException('Body cannot be empty.');
        }

        $format   = $request->getFormat();
        $basePath = $request->getBasePath();
        $branchId = $request->getBranchId();
        $data     = $request->getData();

        $this->logger?->info(
            'CodeCoverageReport: {name}, {basePath}, {hash}, {branchId}, {format}, body size: {size}',
            [
                'name'     => $repositoryName,
                'hash'     => $commitHash,
                'branchId' => $branchId,
                'basePath' => $basePath,
                'format'   => $format,
                'size'     => strlen($data)
            ]
        );

        $report = $this->reportFactory->parse($repository, $commitHash, $branchId, $format, $basePath, $data);

        $this->logger?->info(
            'CodeCoverageReport: {name}, creating report with {count} files.',
            ['name' => $repositoryName, 'count' => count($report->getFiles())]
        );

        // save new report
        $this->reportRepository->save($report, true);

        return new JsonResponse(['created' => count($report->getFiles())], Response::HTTP_OK);
    }
}
