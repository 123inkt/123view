<?php

declare(strict_types=1);

use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\env;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension(
        'framework',
        [
            'assets'               => ['enabled' => false],
            'cache'                => [
                'app'       => 'cache.adapter.filesystem',
                'directory' => '%kernel.cache_dir%/pools',
                'pools'     => [
                    ['name' => 'upsource.cache', 'default_lifetime' => 3600],
                    ['name' => 'gitlab.cache', 'default_lifetime' => 3600]
                ]
            ],
            'http_client'          => [
                'default_options' => [
                    'verify_host' => env('HTTP_CLIENT_VERIFY_HOST')->bool(),
                    'verify_peer' => env('HTTP_CLIENT_VERIFY_PEER')->bool(),
                ],
                'scoped_clients'  => [
                    'upsource.client' => [
                        'auth_basic' => '%env(UPSOURCE_BASIC_AUTH)%',
                        'base_uri'   => '%env(UPSOURCE_API_URL)%~rpc/',
                        'scope'      => 'upsource',
                    ],
                    'gitlab.client'   => [
                        'base_uri' => '%env(GITLAB_API_URL)%api/v4/',
                        'headers'  => ['PRIVATE-TOKEN' => '%env(GITLAB_ACCESS_TOKEN)%'],
                        'scope'    => 'gitlab'
                    ],
                ],
            ],
            'http_method_override' => false,
            'mailer'               => [
                'dsn'      => '%env(MAILER_DSN)%',
                'envelope' => ['sender' => '%env(MAILER_SENDER)%'],
                'headers'  => ['from' => '%env(MAILER_SENDER)%']
            ],
            'php_errors'           => ['log' => true, 'throw' => true],
            'profiler'             => ['enabled' => false],
            'property_access'      => [
                'magic_call'                               => false,
                'magic_get'                                => false,
                'magic_set'                                => false,
                'throw_exception_on_invalid_index'         => true,
                'throw_exception_on_invalid_property_path' => true,
            ],
            'router'               => ['enabled' => false, 'utf8' => true],
            'secret'               => '%env(APP_SECRET)%',
            'translator'           => ['enabled' => false],
            'validation'           => ['enabled' => false]
        ]
    );
};
