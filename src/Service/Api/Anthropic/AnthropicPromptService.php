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
        #[Autowire(env: 'ANTHROPIC_TEMPERATURE')] private float $temperature,
        private Client $client,
    ) {
    }

    public function prompt(string $message): PromptResponse
    {
        $response = $this->client->messages()->create(
            [
                'model'       => $this->model,
                'max_tokens'  => $this->maxTokens,
                'messages'    => [
                    ['role' => 'user', 'content' => $message],
                ],
                'system'      => 'You are an expert software developer. You will be provided a code review request delimited by triple backticks. ' .
                    'Provide code review comments in markdown format. ' .
                    'Each comment should start with a header indicating the file path and line number in the ' .
                    'format "## FILE: path/to/file.php:line-number". When comment relates to multiple lines, only list the first line number. ' .
                    'Follow this with "## COMMENT:" and then your comment. Separate multiple comments with "---". Ensure comments are ' .
                    'concise and relevant to the code provided. Prioritize coding errors, potential bugs, and best practices.',
                'temperature' => $this->temperature,
            ]
        );

        return new PromptResponse($response['content'][0]['text'] ?? '', $response);
    }
}
