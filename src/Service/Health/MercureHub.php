<?php
declare(strict_types=1);

namespace DR\Review\Service\Health;

use Laminas\Diagnostics\Check\AbstractCheck;
use Laminas\Diagnostics\Result\Failure;
use Laminas\Diagnostics\Result\ResultInterface;
use Laminas\Diagnostics\Result\Success;
use Override;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

/**
 * @codeCoverageIgnore
 */
class MercureHub extends AbstractCheck
{
    public function __construct(private readonly HttpClientInterface $httpClient, private readonly HubInterface $hub)
    {
    }

    /**
     * @throws Throwable
     */
    #[Override]
    public function check(): ResultInterface
    {
        $response = $this->httpClient->request('POST', $this->hub->getPublicUrl());
        if (in_array($response->getStatusCode(), [200, 401], true) === false) {
            return new Failure('Mercure hub is not reachable');
        }

        return new Success();
    }
}
