<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Tests\AbstractApiTestCase;
use DR\Review\Tests\DataFixtures\PatchCommentApiFixtures;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use DR\Utils\Assert;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
class PatchCommentEndpointTest extends AbstractApiTestCase
{
    private ?\ApiPlatform\Symfony\Bundle\Test\Response $lastResponse = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client->disableReboot();
    }

    public function testAuthorMessageKeepsOmittedTag(): void
    {
        $comment = $this->getComment('patch author final');

        $this->request($comment, ['message' => 'Updated message']);

        $this->assertPatchStatusCode(Response::HTTP_OK);
        $this->assertPatchJsonContains(['message' => 'Updated message', 'tag' => 'suggestion']);
        $response = Json::decode($this->getBrowserResponseContent(), true);
        self::assertSame([
            'id',
            'userId',
            'reviewId',
            'message',
            'filepath',
            'line',
            'sha',
            'state',
            'tag',
            'createdAt',
            'updatedAt',
        ], array_keys(Assert::isArray($response)));
    }

    public function testAuthorCanSetChangeAndClearTag(): void
    {
        $comment = $this->getComment('patch author final');

        $this->request($comment, ['tag' => 'change_request']);
        $this->assertPatchStatusCode(Response::HTTP_OK);
        $this->assertPatchJsonContains(['tag' => 'change_request']);

        $comment = $this->reload($comment);
        $this->request($comment, ['tag' => 'nice_to_have']);
        $this->assertPatchStatusCode(Response::HTTP_OK);
        $this->assertPatchJsonContains(['tag' => 'nice_to_have']);

        $comment = $this->reload($comment);
        $this->request($comment, ['tag' => null]);
        $this->assertPatchStatusCode(Response::HTTP_OK);
        $this->assertPatchJsonContains(['tag' => null]);
        self::assertNull($this->reload($comment)->getTag());
    }

    public function testAuthorPatchesMessageTagAndState(): void
    {
        $comment = $this->getComment('patch author final');

        $this->request($comment, ['message' => 'Combined update', 'tag' => 'explanation', 'state' => 'resolved']);

        $this->assertPatchStatusCode(Response::HTTP_OK);
        $this->assertPatchJsonContains(['message' => 'Combined update', 'tag' => 'explanation', 'state' => 'resolved']);

        self::assertCount(1, $this->messagesOfType(CommentUpdated::class));
        self::assertCount(1, $this->messagesOfType(CommentResolved::class));
    }

    public function testOtherUserTogglesFinalCommentState(): void
    {
        $comment = $this->getComment('patch author final');

        $this->request($comment, ['state' => 'resolved'], $this->otherToken());
        $this->assertPatchStatusCode(Response::HTTP_OK);
        $this->assertPatchJsonContains(['state' => 'resolved']);

        self::assertCount(1, $this->messagesOfType(CommentResolved::class));
    }

    public function testNonAuthorCannotChangeMessageOrTag(): void
    {
        $comment = $this->getComment('patch author final');

        $this->request($comment, ['message' => 'Unauthorized edit'], $this->otherToken());
        $this->assertPatchStatusCode(Response::HTTP_FORBIDDEN);
        $comment = $this->reload($comment);
        self::assertSame('patch author final', $comment->getMessage());
    }

    public function testMixedNonAuthorUpdateFailsAtomically(): void
    {
        $comment           = $this->getComment('patch author final');
        $originalTimestamp = $comment->getUpdateTimestamp();

        $this->request($comment, ['message' => 'Unauthorized edit', 'state' => 'resolved'], $this->otherToken());

        $this->assertPatchStatusCode(Response::HTTP_FORBIDDEN);
        $comment = $this->reload($comment);
        self::assertSame('patch author final', $comment->getMessage());
        self::assertSame('open', $comment->getState()->value);
        self::assertSame($originalTimestamp, $comment->getUpdateTimestamp());
    }

    public function testAuthorEditsOwnDraftButNotState(): void
    {
        $comment = $this->getComment('patch author draft');

        $this->request($comment, ['message' => 'Updated draft', 'tag' => null]);
        $this->assertPatchStatusCode(Response::HTTP_OK);
        $this->assertPatchJsonContains(['message' => 'Updated draft', 'tag' => null, 'state' => 'open']);
    }

    public function testForeignDraftNotFoundBeforeAuth(): void
    {
        $comment = $this->getComment('patch other draft');

        $this->request($comment, ['message' => 'Unauthorized edit']);

        $this->assertPatchStatusCode(Response::HTTP_NOT_FOUND);
        self::assertSame('patch other draft', $this->reload($comment)->getMessage());
    }

    public function testUnchangedValueRefreshesTimestamp(): void
    {
        $comment           = $this->getComment('patch author final');
        $originalTimestamp = $comment->getUpdateTimestamp();

        $this->request($comment, ['state' => 'open']);

        $this->assertPatchStatusCode(Response::HTTP_OK);
        self::assertGreaterThan($originalTimestamp, $this->reload($comment)->getUpdateTimestamp());
    }

    public function testRejectsMissingEmptyAndUnknownInput(): void
    {
        $comment = $this->getComment('patch author final');
        $this->request($comment, []);
        $this->assertPatchStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->requestToId(987654, ['message' => 'Update']);
        $this->assertPatchStatusCode(Response::HTTP_NOT_FOUND);

        $this->request($comment, ['userId' => 123]);
        $this->assertPatchStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        self::assertSame('patch author final', $this->reload($comment)->getMessage());
    }

    public function testRequiresAuthentication(): void
    {
        $comment = $this->getComment('patch author final');

        $response           = $this->client->request(Request::METHOD_PATCH, '/api/comments/' . $comment->getId(), [
            'headers' => ['content-type' => ['application/merge-patch+json']],
            'body'    => json_encode(['message' => 'Update'], JSON_THROW_ON_ERROR),
        ]);
        $this->lastResponse = Assert::isInstanceOf($response, \ApiPlatform\Symfony\Bundle\Test\Response::class);

        $this->assertPatchStatusCode(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function request(Comment $comment, array $payload, ?string $token = null): void
    {
        $this->requestToId($comment->getId(), $payload, $token ?? UserAccessTokenFixtures::TOKEN_VALUE);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function requestToId(int $id, array $payload, ?string $token = UserAccessTokenFixtures::TOKEN_VALUE): void
    {
        $response           = $this->client->request(Request::METHOD_PATCH, '/api/comments/' . $id, [
            'headers' => [
                'authorization' => 'Bearer ' . $token,
                'content-type'  => 'application/merge-patch+json',
            ],
            'json'    => $payload,
        ]);
        $this->lastResponse = Assert::isInstanceOf($response, \ApiPlatform\Symfony\Bundle\Test\Response::class);
    }

    private function otherToken(): string
    {
        return str_pad(PatchCommentApiFixtures::OTHER_USER_TOKEN, 80, '0');
    }

    private function getComment(string $message): Comment
    {
        return Assert::notNull(self::getService(CommentRepository::class)->findOneBy(['message' => $message]));
    }

    private function reload(Comment $comment): Comment
    {
        $this->entityManager?->clear();

        return Assert::notNull(self::getService(CommentRepository::class)->find($comment->getId()));
    }

    private function assertPatchStatusCode(int $expectedStatusCode): void
    {
        self::assertNotNull($this->lastResponse, 'A request must have been made before asserting its response.');
        self::assertSame($expectedStatusCode, $this->lastResponse->getStatusCode(), $this->lastResponse->getContent(false));
    }

    /**
     * @param array<string, mixed> $expectedFields
     */
    private function assertPatchJsonContains(array $expectedFields): void
    {
        /** @var array<string, mixed> $response */
        $response = Json::decode($this->getBrowserResponseContent(), true);
        foreach ($expectedFields as $field => $value) {
            self::assertArrayHasKey($field, $response);
            self::assertSame($value, $response[$field]);
        }
    }

    private function getBrowserResponseContent(): string
    {
        self::assertNotNull($this->lastResponse, 'A request must have been made before reading its response.');

        return $this->lastResponse->getContent(false);
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [PatchCommentApiFixtures::class];
    }
}
