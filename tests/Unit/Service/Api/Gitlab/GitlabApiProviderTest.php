<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\User\GitAccessToken;
use DR\Review\Entity\User\User;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\GitlabApiProvider;
use DR\Review\Service\Api\Gitlab\OAuth2Authenticator;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

#[CoversClass(GitlabApiProvider::class)]
class GitlabApiProviderTest extends AbstractTestCase
{
    private HttpClientInterface&MockObject $httpClient;
    private SerializerInterface&MockObject $serializer;
    private OAuth2Authenticator&MockObject $authenticator;
    private GitlabApiProvider              $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient    = $this->createMock(HttpClientInterface::class);
        $this->serializer    = $this->createMock(SerializerInterface::class);
        $this->authenticator = $this->createMock(OAuth2Authenticator::class);
        $this->provider      = new GitlabApiProvider(
            'https://example.com/',
            $this->logger,
            $this->httpClient,
            $this->serializer,
            $this->authenticator
        );
    }

    /**
     * @throws Throwable
     */
    public function testCreateOnlySupportGitlabRepositories(): void
    {
        $repository = new Repository();
        $repository->setGitType(RepositoryGitType::GITHUB);

        $user = new User();

        static::assertNull($this->provider->create($repository, $user));
    }

    /**
     * @throws Throwable
     */
    public function testCreateRequireGitAccessToken(): void
    {
        $repository = new Repository();
        $repository->setGitType(RepositoryGitType::GITLAB);

        $user = new User();
        $user->setEmail('email');

        static::assertNull($this->provider->create($repository, $user));
    }

    /**
     * @throws Throwable
     */
    public function testCreateWithOptions(): void
    {
        $repository = new Repository();
        $repository->setGitType(RepositoryGitType::GITLAB);

        $token = new GitAccessToken();
        $token->setGitType(RepositoryGitType::GITLAB);

        $user = new User();
        $user->setEmail('email');
        $user->getGitAccessTokens()->add($token);

        $this->authenticator->expects($this->once())->method('getAuthorizationHeader')->with($token)->willReturn('Bearer token');
        $this->httpClient->expects($this->once())->method('withOptions')
            ->with(
                [
                    'base_uri'      => 'https://example.com/api/v4/',
                    'max_redirects' => 0,
                    'headers'       => ['Authorization' => 'Bearer token'],
                ]
            )
            ->willReturnSelf();

        $expected = new GitlabApi($this->logger, $this->httpClient, $this->serializer);
        static::assertEquals($expected, $this->provider->create($repository, $user));
    }
}
