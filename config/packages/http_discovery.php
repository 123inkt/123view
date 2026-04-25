<?php

declare(strict_types=1);

use Http\Discovery\Psr17Factory;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UploadedFileFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'services' => [
        RequestFactoryInterface::class       => '@http_discovery.psr17_factory',
        ResponseFactoryInterface::class      => '@http_discovery.psr17_factory',
        ServerRequestFactoryInterface::class => '@http_discovery.psr17_factory',
        StreamFactoryInterface::class        => '@http_discovery.psr17_factory',
        UploadedFileFactoryInterface::class  => '@http_discovery.psr17_factory',
        UriFactoryInterface::class           => '@http_discovery.psr17_factory',
        'http_discovery.psr17_factory'       => ['class' => Psr17Factory::class, 'autowire' => false],
    ],
]);
