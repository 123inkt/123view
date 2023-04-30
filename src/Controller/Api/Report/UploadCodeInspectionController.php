<?php
declare(strict_types=1);

namespace DR\Review\Controller\Api\Report;

use DR\Review\Controller\AbstractController;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Repository\Report\CodeInspectionReportRepository;
use DR\Review\Request\Report\UploadCodeInspectionRequest;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Report\CodeInspection\CodeInspectionReportFactory;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UploadCodeInspectionController extends AbstractController
{
    public function __construct(
        private readonly RepositoryRepository $repositoryRepository,
        private readonly CodeInspectionReportRepository $reportRepository,
        private readonly CodeInspectionReportFactory $reportFactory
    ) {
    }

    #[Route('/api/report/code-inspection/{repositoryName<[a-z0-9-]+>}/{commitHash<[a-zA-Z0-9]{6,255}>}', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(UploadCodeInspectionRequest $request, string $repositoryName, string $commitHash): Response
    {
        $repository = $this->repositoryRepository->findOneBy(['name' => $repositoryName]);
        if ($repository === null) {
            throw new NotFoundHttpException();
        }

        $report = $this->reportFactory->parse(
            $repository,
            $commitHash,
            $request->getIdentifier(),
            $request->getFormat(),
            $request->getBasePath(),
            $request->getData()
        );

        // remove existing report
        $this->reportRepository->removeOneBy(
            ['repository' => $repository, 'inspectionId' => $request->getIdentifier(), 'commitHash' => $commitHash],
            flush: true
        );

        // save new report
        $this->reportRepository->save($report, true);

        return new JsonResponse(['created' => count($report->getIssues())], Response::HTTP_OK);
    }
}
