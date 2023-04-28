<?php

declare(strict_types=1);

use ApiPlatform\OpenApi\Model\MediaType;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\OpenApi;

// phpcs:ignorefile
return static function (OpenApi $openApi) {
    $content = new ArrayObject();
    $content->offsetSet(
        "application/json",
        new MediaType(new ArrayObject(['type' => 'string']), "{}")
    );
    $content->offsetSet(
        "application/xml",
        new MediaType(new ArrayObject(['type' => 'string']), "<?xml version='1.0' encoding='UTF-8'?>\n><coverage></coverage>")
    );

    $operation = new Operation(
        operationId: 'uploadTestCoverage',
        tags       : ['TestCoverage'],
        responses  : [
                         204 => new Response('when the request has been successfully processed'),
                         400 => new Response('on any invalid arguments'),
                         404 => new Response('when the repository with the given `name` cant be found'),
                     ],
        summary    : "Upload test coverage report",
        description: "Upload test coverage report which will be made available in reviews matching the `match`",
        parameters : [
                         new Parameter(
                                     'repository',
                                     'path',
                                     'The name of the repository to which the coverage belongs to.',
                                     true,
                             schema: ['type' => 'string']
                         ),
                         new Parameter(
                                     'match',
                                     'query',
                                     'The coverage will be visible in the reviews that contain this string in the title.',
                                     true,
                             schema: ['type' => 'string']
                         ),
                         new Parameter(
                                     'basePath',
                                     'query',
                                     'The `basePath` to subtract from the filenames in the report.',
                             schema: ['type' => 'string']
                         ),
                         new Parameter(
                                     'format',
                                     'query',
                                     'The format of the input. Defaults to `cobertura`.',
                             schema: [
                                         'type' => 'string',
                                         'enum' => ['cobertura', 'clover']
                                     ]
                         )

                     ],
        requestBody: new RequestBody(
                         'The `xml` or `json` of the test coverage in the in `format` specified format.',
                         $content,
                         true
                     )
    );

    $openApi->getPaths()->addPath('/api/test-coverage/{repository}', new PathItem(post: $operation));
};
