<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Model\Api\Gitlab\User;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class Users
{
    public function __construct(
        private readonly LoggerInterface $logger,
        private readonly HttpClientInterface $gitlabClient,
        private readonly SerializerInterface $serializer
    ) {
    }

    /**
     * @throws Throwable
     */
    public function getUser(int $userId): ?User
    {
        $response = $this->gitlabClient->request('GET', sprintf('users/%d', $userId));
        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $this->logger->debug('Failed to find user with id {id}', ['id' => $userId]);

            return null;
        }

        /** @phpstan-var User */
        return $this->serializer->deserialize(
            $response->getContent(false),
            User::class,
            JsonEncoder::FORMAT,
            [AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => true]
        );
    }
}
