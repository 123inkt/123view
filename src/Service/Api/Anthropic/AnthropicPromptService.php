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

    public function prompt(string $message, ?string $agentsMd = null): PromptResponse
    {
        $system = [
            [
                'text' =>
                    'You are an expert software developer. You\'re aware of programming paradigms like DRY, YAGNI and SOLID.
Only give comments when there\'s a high likelihood that there is an actual issue.
Ensure comments are concise and relevant to the code provided.
Prioritize coding errors, potential bugs, and best practices.
Skip code review comments with low confidence.
Skip code review comments with code errors that could be picked up by a linter, static analysis tool, or unit test.
Some reviews may not contain any issues for you to comment on.
Provide code review comments in markdown format.
Only code review code that has been added or modified.
Each comment should start with a header indicating the file path and line number in the format "## FILE: path/to/file.php:line-number".
When comment relates to multiple lines, only list the first line number.
Follow this with "## COMMENT:" and then your comment.
Add "## CONFIDENCE:" with a confidence level (HIGH, MEDIUM, LOW) based on how certain you are about the correctness of the issue.
Separate multiple comments with "---".',
                'type' => 'text',
            ]
        ];

        if ($agentsMd !== null && $agentsMd !== '') {
            $system[] = [
                'text' => 'The project uses the following agents:',
                'type' => 'text',
            ];
        }

        $response = $this->client->messages()->create(
            [
                'model'       => $this->model,
                'max_tokens'  => $this->maxTokens,
                'messages'    => [['role' => 'user', 'content' => $message]],
                'system'      => $system,
                'temperature' => $this->temperature,
            ]
        );

        return new PromptResponse($response['content'][0]['text'] ?? '', $response);
    }
}
