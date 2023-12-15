<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\User;

use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class TestController
{
    public function __construct(
        //private readonly string $token,
        private readonly HttpClientInterface $client,
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
        $result = [];
        foreach ([2558, 2559] as $commentId) {
            $comment = $this->commentRepository->find($commentId);

            $lineReference = $comment->getLineReference();

            $body = [
                'position[position_type]' => 'text',
                'position[base_sha]'      => "9e2b792a5ea17fb5f3c5acca1e87497e77c494de",
                'position[head_sha]'      => "f4cd0e8067e9c53928f05f35a6834c4c2799a68c",
                'position[start_sha]'     => "9e2b792a5ea17fb5f3c5acca1e87497e77c494de",
                'position[new_path]'      => $lineReference->oldPath,
                'position[old_path]'      => $lineReference->newPath,
                'body'                    => $comment->getMessage()
            ];

            if ($lineReference->state === LineReferenceStateEnum::Added) {
                $body['position[new_line]'] = $lineReference->lineAfter;
            } elseif ($lineReference->state === LineReferenceStateEnum::Deleted) {
                $body['position[old_line]'] = $lineReference->line;
            } elseif ($lineReference->state === LineReferenceStateEnum::Modified) {
                $body['position[new_line]'] = $lineReference->lineAfter;
            } else {
                $body['position[old_line]'] = $lineReference->line;
                $body['position[new_line]'] = $lineReference->lineAfter;
            }

            $response = $this->client->request(
                'POST',
                'https://gitlab.123dev.nl/api/v4/projects/72/merge_requests/178/discussions',
                ['headers' => ['PRIVATE-TOKEN' => $token,], 'body' => $body]
            );

            $result[] = $response->toArray(false);
        }

        return new JsonResponse($result);
    }
}
