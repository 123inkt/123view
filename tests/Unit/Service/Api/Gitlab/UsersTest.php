<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Model\Api\Gitlab\User;
use DR\Review\Service\Api\Gitlab\Users;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;
use Throwable;

#[CoversClass(Users::class)]
class UsersTest extends AbstractTestCase
{
    private HttpClientInterface&MockObject $httpClient;
    private SerializerInterface&MockObject $serializer;
    private Users                          $users;

    protected function setUp(): void
    {
        parent::setUp();
        $logger           = $this->createMock(LoggerInterface::class);
        $this->httpClient = $this->createMock(HttpClientInterface::class);
        $this->serializer = $this->createMock(SerializerInterface::class);
        $this->users      = new Users($logger, $this->httpClient, $this->serializer);
    }

    /**
     * @throws Throwable
     */
    public function testGetUserSuccess(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::HTTP_OK);
        $response->expects($this->once())->method('getContent')->willReturn('{"foo":"bar"}');

        $user = new User();

        $this->httpClient->expects($this->once())->method('request')->with('GET', 'users/123')->willReturn($response);
        $this->serializer->expects($this->once())
            ->method('deserialize')
            ->with('{"foo":"bar"}', User::class, JsonEncoder::FORMAT, [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true])
            ->willReturn($user);

        static::assertSame($user, $this->users->getUser(123));
    }

    /**
     * @throws Throwable
     */
    public function testGetUserFailure(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->expects($this->once())->method('getStatusCode')->willReturn(Response::HTTP_BAD_REQUEST);

        $this->httpClient->expects($this->once())->method('request')->with('GET', 'users/123')->willReturn($response);
        $this->serializer->expects($this->never())->method('deserialize');

        static::assertNull($this->users->getUser(123));
    }
}
