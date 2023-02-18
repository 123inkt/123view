<?php

declare(strict_types=1);

use Symfony\Config\ApiPlatformConfig;

return static function (ApiPlatformConfig $config): void {
    $config->title('123view API')
        ->version('1.0.0')
        ->showWebby(false)
        ->pathSegmentNameGenerator('api_platform.path_segment_name_generator.dash');

    $config->mapping()->paths(['%kernel.project_dir%/src/Entity']);

    $config->swagger()->versions([3])->apiKeys('foo')->name('Authorization')->type('header');

    $config->oauth()->enabled(true)->flow('password')->tokenUrl('/oauth/v2/token');

    $config->openapi()->contact()->name('')->url('')->email('');

    $config->formats('json')->mimeTypes(['application/json']);
    $config->formats('html')->mimeTypes(['text/html']);

    $config->defaults()
        // The default number of items per page
        ->paginationItemsPerPage(30)
        // The default maximum number of items per page
        ->paginationMaximumItemsPerPage(50)
        // The default cache headers
        ->cacheHeaders(['etag' => false]);
};
