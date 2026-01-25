<?php

declare(strict_types=1);

use Symfony\Config\AiConfig;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (AiConfig $config): void {
    // configure platform
    $config->platform()->anthropic()->apiKey(env('AI_API_KEY'));

    // configure agent
    $config->agent('default')
        ->platform('ai.platform.anthropic')
        ->model(env('AI_AGENT_MODEL'))
        ->prompt()->file('%kernel.project_dir%/resources/ai-prompt/code-review-agent.md');
};
