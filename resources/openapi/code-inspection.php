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
        operationId: 'uploadCodeInspection',
        tags       : ['Report'],
        responses  : [
                         204 => new Response('When the request has been successfully processed'),
                         400 => new Response('On any invalid arguments'),
                         404 => new Response('When the repository with the given `name` cant be found'),
                     ],
        summary    : "Upload code inspection report",
        description: "Upload code inspection report in varies formats",
        parameters : [
                         new Parameter(
                                     'repository',
                                     'path',
                                     'The name of the repository to which the report belongs to.',
                                     true,
                             schema: ['type' => 'string']
                         ),
                         new Parameter(
                                     'commitHash',
                                     'query',
                                     'The hash of the commit this report belongs to.',
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
                                     'The format of the input. Defaults to `checkstyle`.',
                             schema: [
                                         'type' => 'string',
                                         'enum' => ['checkstyle', 'gitlab']
                                     ]
                         )

                     ],
        requestBody: new RequestBody(
                         'The `xml` or `json` of the code inspection specified by the `format`.',
                         $content,
                         true
                     )
    );

    $openApi->getPaths()->addPath('/api/report/code-inspection/{repository}', new PathItem(post: $operation));
};
