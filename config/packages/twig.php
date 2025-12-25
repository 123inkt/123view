<?php

declare(strict_types=1);

use Symfony\Config\TwigConfig;

return static function (TwigConfig $twig): void {
    $twig
        ->defaultPath('%kernel.project_dir%/templates')
        ->path('%kernel.project_dir%/assets/styles', 'styles')
        ->formThemes(['bootstrap_5_layout.html.twig']);

    $twig->global('app_name')->value('%env(APP_NAME)%');
    $twig->global('app_absolute_url')->value('%env(APP_ABSOLUTE_URL)%');
    $twig->global('app_auth_password')->value('%env(bool:APP_AUTH_PASSWORD)%');
    $twig->global('app_auth_azure_ad')->value('%env(bool:APP_AUTH_AZURE_AD)%');
    $twig->global('ai_code_review_enabled')->value('%env(bool:AI_CODE_REVIEW_ENABLED)%');
};
