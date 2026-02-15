<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return App::config([
    'ai' => [
        'platform' => ['anthropic' => ['api_key' => env('AI_API_KEY')]],
        'agent'    => [
            'default' => [
                'platform' => 'ai.platform.anthropic',
                'model'    => env('AI_AGENT_MODEL'),
                'prompt'   => ['file' => '%kernel.project_dir%/resources/ai-prompt/code-review-agent.md'],
            ],
        ],
    ],
]);
