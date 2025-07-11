<?php

declare(strict_types=1);

use Symfony\Config\ApiPlatformConfig;

return static function (ApiPlatformConfig $config): void {
    $config->title('%env(APP_NAME)% API')
        ->version('1.0.0')
        ->showWebby(true)
        ->pathSegmentNameGenerator('api_platform.metadata.path_segment_name_generator.dash');

    $config->mapping()->paths(['%kernel.project_dir%/src/Entity', '%kernel.project_dir%/src/ViewModel']);

    $config->swagger()->versions([3])->apiKeys('Bearer')->name('Authorization')->type('header');

    $config->mercure()->enabled(false);
    $config->messenger()->enabled(false);

    $config->formats('json')->mimeTypes(['application/json']);
    $config->formats('html')->mimeTypes(['text/html']);

    $config->patchFormats('json')->mimeTypes(['application/merge-patch+json']);

    $config->defaults()
        // allow custom pagination parameters client side
        ->paginationClientEnabled(false)
        ->paginationClientItemsPerPage(true)
        // The default number of items per page
        ->paginationItemsPerPage(30)
        // The default maximum number of items per page
        ->paginationMaximumItemsPerPage(100);
};
