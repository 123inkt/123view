<?php
declare(strict_types=1);

namespace DR\Review\Tests\Integration\Service\Api\Gitlab;

use DR\Review\Service\Api\Gitlab\MergeRequests;
use DR\Review\Tests\AbstractKernelTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

#[CoversClass(MergeRequests::class)]
class MergeRequestsNotesSerializationTest extends AbstractKernelTestCase
{
    /**
     * @throws Throwable
     */
    public function testNotesWithNullAvatarUrl(): void
    {
        $json = '[{"id":1,"body":"note","author":{"id":2,"name":"name","username":"user","avatar_url":null}}]';

        $response = static::createStub(ResponseInterface::class);
        $response->method('getContent')->willReturn($json);

        /** @var HttpClientInterface&MockObject $client */
        $client = $this->createMock(HttpClientInterface::class);
        $client->expects($this->once())->method('request')->willReturn($response);

        $serializer = static::getContainer()->get(SerializerInterface::class);
        $mergeRequests = new MergeRequests($client, $serializer);

        $notes = $mergeRequests->notes(111, 222);

        static::assertCount(1, $notes);
        static::assertSame('user', $notes[0]->author?->username);
        static::assertNull($notes[0]->author->avatarUrl);
    }
}
