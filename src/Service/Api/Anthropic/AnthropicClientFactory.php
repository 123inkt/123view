<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Anthropic;

use Anthropic;
use Anthropic\Client;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class AnthropicClientFactory
{
    public function __construct(#[Autowire(env: 'ANTHROPIC_API_KEY')] private string $apiKey)
    {
    }

    public function create(): Client
    {
        return Anthropic::client($this->apiKey);
    }

}
