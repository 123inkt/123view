<?php

declare(strict_types=1);

use ApiPlatform\OpenApi\Model\MediaType;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\OpenApi;
use DR\Review\Service\Report\Coverage\Parser\CoberturaParser;

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
        operationId: 'UploadCodeCoverage',
        tags       : ['Report'],
        responses  : [
            200 => new Response('When the report was successfully created. Responds with count of created issues.'),
            204 => new Response('When the report has no issues.'),
            400 => new Response('On any invalid arguments.'),
            404 => new Response('When the repository with the given `name` cant be found.'),
        ],
        summary    : "Upload code coverage report",
        description: "Upload code coverage report in varies formats. **Requires ADMIN privileges.**",
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
                'path',
                'The hash of the commit this report belongs to.',
                true,
                schema: ['type' => 'string', 'pattern' => '^[a-zA-Z0-9]{6,255}$']
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
                'The format of the input. Defaults to `' . CoberturaParser::FORMAT . '`.',
                schema: [
                    'type' => 'string',
                    'enum' => [CoberturaParser::FORMAT]
                ]
            )
        ],
        requestBody: new RequestBody(
            'The `xml` or `json` of the test coverage in the in `format` specified format.',
            $content,
            true
        )
    );

    $openApi->getPaths()->addPath('/api/report/code-coverage/{repository}/{commitHash}', new PathItem(post: $operation));
};
