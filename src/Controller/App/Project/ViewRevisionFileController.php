<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Project;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Review\Utility\FileUtil;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ViewRevisionFileController extends AbstractController
{
    public function __construct(private readonly LockableGitShowService $showService)
    {
    }

    /**
     * @throws RepositoryException
     */
    #[Route('app/revision/{id<\d+>}/view-file', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] Revision $revision): Response
    {
        $file     = $request->query->getString('file');
        $mimeType = FileUtil::getMimeType($file);
        if ($mimeType === null) {
            throw new BadRequestHttpException(sprintf('Could not determine mime-type for file "%s"', $file));
        }

        $contents = $this->showService->getFileContents($revision, $file, FileUtil::isBinary($mimeType));

        return new Response($contents, Response::HTTP_OK, ['Content-Type' => $mimeType]);
    }
}
