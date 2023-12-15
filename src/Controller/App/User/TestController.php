<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Model\Api\Gitlab\Version;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Utils\Arrays;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class TestController
{
    public function __construct(
        private readonly string $apiUrl,
        private readonly string $token,
        private readonly HttpClientInterface $client,
        private readonly SerializerInterface $serializer,
        private readonly CommentRepository $commentRepository
    ) {
    }

    /**
     * @throws Throwable
     */
    #[Route('/app/user/test', self::class, methods: ['GET', 'POST'])]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request): Response
    {
        $client = $this->client->withOptions(['base_uri' => $this->apiUrl, 'headers' => ['PRIVATE-TOKEN' => $this->token]]);

        $projectId       = 72;
        $mergeRequestIId = 178;
        $api             = new GitlabApi($client, $this->serializer);
        $versions        = $api->mergeRequests()->versions($projectId, $mergeRequestIId);
        /** @var Version $version */
        $version = Arrays::first($versions);

        foreach ([2558, 2559] as $commentId) {
            $comment       = $this->commentRepository->find($commentId);
            $lineReference = $comment->getLineReference();

            $position               = new Position();
            $position->positionType = 'text';
            $position->headSha      = $version->headCommitSha;
            $position->startSha     = $version->startCommitSha;
            $position->baseSha      = $version->baseCommitSha;
            $position->oldPath      = $lineReference->oldPath;
            $position->newPath      = $lineReference->newPath;

            if ($lineReference->state === LineReferenceStateEnum::Added || $lineReference->state === LineReferenceStateEnum::Modified) {
                $position->newLine = $lineReference->lineAfter;
            } elseif ($lineReference->state === LineReferenceStateEnum::Deleted) {
                $position->oldLine = $lineReference->line;
            } else {
                $position->oldLine = $lineReference->line;
                $position->newLine = $lineReference->lineAfter;
            }

            $api->discussions()->create($projectId, $mergeRequestIId, $position, $comment->getMessage());
        }

        return new JsonResponse([]);
    }
}
