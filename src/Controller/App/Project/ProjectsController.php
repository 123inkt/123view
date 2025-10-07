<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Project;

use Doctrine\DBAL\Exception;
use DR\Review\Controller\AbstractController;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Model\Review\DirectoryTreeNode;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Api\Anthropic\AnthropicPromptService;
use DR\Review\Service\Api\Anthropic\AnthropicResponseParser;
use DR\Review\Service\CodeReview\CodeReviewFileService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\Git\Review\FileDiffOptions;
use DR\Review\ViewModel\App\Project\ProjectsViewModel;
use DR\Review\ViewModelProvider\ProjectsViewModelProvider;
use DR\Utils\Assert;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProjectsController extends AbstractController
{
    public function __construct(
        private readonly ProjectsViewModelProvider $viewModelProvider,
        private readonly TranslatorInterface $translator,
        private readonly AnthropicPromptService $promptService,
        private readonly CodeReviewRepository $reviewRepository,
        private CodeReviewRevisionService $revisionService,
        private CodeReviewFileService $fileService,
        private UserRepository $userRepository,
        private CommentRepository $commentRepository,
        private AnthropicResponseParser $responseParser,
    ) {
    }

    /**
     * @return array<string, string|ProjectsViewModel>
     * @throws Exception
     */
    #[Route('app/projects', name: self::class, methods: 'GET')]
    #[Template('app/project/projects.html.twig')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): array
    {
        $review    = $this->reviewRepository->find(24588);
        $revisions = $this->revisionService->getRevisions($review);

        // get diff files for review
        /** @var DirectoryTreeNode<DiffFile> $fileTree */
        [$fileTree] = $this->fileService->getFiles(
            $review,
            $revisions,
            null,
            new FileDiffOptions(
                FileDiffOptions::DEFAULT_LINE_DIFF,
                DiffComparePolicy::ALL,
                CodeReviewType::COMMITS,
                null
            )
        );
        $files = $fileTree->getFilesRecursive();

        $files = array_filter($files, fn(DiffFile $file) => str_contains($file->getPathname(), 'baseline') === false);
        $diffs = array_map(fn(DiffFile $file) => $file->raw, $files);

        $result = $this->promptService->prompt(
            "Review the follow code.\n" . implode("\n", $diffs),
            sprintf('code-review-%d', $review->getId())
        );

        $test = true;

        $responses = $this->responseParser->parse($result->message);
        $user      = Assert::notNull($this->userRepository->find(102));

        foreach ($responses as $response) {
            $comment = new Comment();
            $comment->setFilePath($response->filepath);
            $comment->setTag(null);
            $comment->setLineReference(new LineReference(newPath: $response->filepath, lineAfter: $response->lineNumber));
            $comment->setReview($review);
            $comment->setMessage($response->message);
            $comment->setUser($user);
            $comment->setCreateTimestamp(time());
            $comment->setUpdateTimestamp(time());

            $review->getComments()->add($comment);
            $this->commentRepository->save($comment, true);
        }

        return [
            'page_title'    => $this->translator->trans('projects'),
            'projectsModel' => $this->viewModelProvider->getProjectsViewModel(trim($request->query->get('search', '')))
        ];
    }
}
