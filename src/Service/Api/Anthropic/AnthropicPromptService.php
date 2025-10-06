<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Anthropic;

use Anthropic\Client;
use DR\Review\Model\Api\Anthropic\PromptResponse;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

readonly class AnthropicPromptService
{
    public function __construct(
        #[Autowire(env: 'ANTHROPIC_MODEL')] private string $model,
        #[Autowire(env: 'ANTHROPIC_MAX_TOKENS')] private int $maxTokens,
        private Client $client,
    ) {
    }

    public function prompt(string $message): PromptResponse
    {
        $response = $this->client->messages()->create(
            [
                'model'      => $this->model,
                'max_tokens' => $this->maxTokens,
                'messages'   => [
                    ['role' => 'user', 'content' => $message],
                ],
            ]
        );

        return new PromptResponse($response['content'][0]['text'] ?? '', $response);
    }
}
