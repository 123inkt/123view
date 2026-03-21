<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config(
    [
        'twig' => [
            'default_path' => '%kernel.project_dir%/templates',
            'paths'        => ['%kernel.project_dir%/assets/styles' => 'styles'],
            'form_themes'  => ['bootstrap_5_layout.html.twig'],
            'globals'      => [
                'app_name'               => '%env(APP_NAME)%',
                'app_absolute_url'       => '%env(APP_ABSOLUTE_URL)%',
                'app_auth_password'      => '%env(bool:APP_AUTH_PASSWORD)%',
                'app_auth_azure_ad'      => '%env(bool:APP_AUTH_AZURE_AD)%',
                'ai_code_review_enabled' => '%env(bool:AI_CODE_REVIEW_ENABLED)%',
            ],
        ]
    ]
);
