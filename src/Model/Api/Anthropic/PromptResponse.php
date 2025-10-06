<?php
declare(strict_types=1);

namespace DR\Review\Model\Api\Anthropic;

use Anthropic\Responses\Messages\CreateResponse;


readonly class PromptResponse
{
    /**
     * @codeCoverageIgnore Simple DTO
     */
    public function __construct(public string $message, public CreateResponse $response)
    {
    }
}
