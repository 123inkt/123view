<?php

declare(strict_types=1);

use Chrisguitarguy\RequestId\Generator\RamseyUuid4Generator;
use Chrisguitarguy\RequestId\SimpleIdStorage;
use Symfony\Config\ChrisguitarguyRequestIdConfig;

return static function (ChrisguitarguyRequestIdConfig $config): void {
    // Whether or not to trust the incoming request header. This is turned
    // on by default. If true a value in the `Request-Id` header in the request
    // will be used as the request ID for the rest of the request. If false
    // those values are ignored.
    $config->trustRequestHeader(true);

    // The header which the bundle inspects for the incoming request ID
    // if this is not set an ID will be generated and set at this header
    $config->requestHeader('Request-Id');

    // The header which the bundle will set the request ID to on the response
    $config->responseHeader('Request-Id');

    // The service key of an object that implements
    // Chrisguitarguy\RequestId\RequestIdStorage
    $config->storageService(SimpleIdStorage::class);

    # The service key of an object that implements
    # Chrisguitarguy\RequestId\RequestIdGenerator
    $config->generatorService(RamseyUuid4Generator::class);

    // Whether or not to add the monolog process, defaults to true
    $config->enableMonolog(true);

    # Whether or not to add the twig extension, defaults to true
    $config->enableTwig(true);
};
