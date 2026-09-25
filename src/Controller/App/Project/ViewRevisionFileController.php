<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Project;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Exception\RepositoryException;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Review\Service\Markdown\MarkdownConverterService;
use DR\Review\Utility\MimeTypes;
use League\CommonMark\Exception\CommonMarkException;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ViewRevisionFileController extends AbstractController
{
    public function __construct(private readonly LockableGitShowService $showService, private readonly MarkdownConverterService $converter)
    {
    }

    /**
     * @throws RepositoryException
     * @throws CommonMarkException
     */
    #[Route('app/revision/{id<\d+>}/view-file', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] Revision $revision): Response
    {
        $file     = $request->query->getString('file');
        $mimeType = MimeTypes::getMimeType($file) ?? 'text/plain';
        $contents = $this->showService->getFileContents($revision, $file, true);

        if ($mimeType === 'text/markdown') {
            return new Response($this->converter->convert($contents), Response::HTTP_OK, ['Content-Type' => 'text/html']);
        }

        return new Response($contents, Response::HTTP_OK, ['Content-Type' => $mimeType, 'Cache-Control' => 'public']);
    }
}
