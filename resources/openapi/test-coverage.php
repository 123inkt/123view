<?php

declare(strict_types=1);

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;

// phpcs:ignorefile
return static function (OpenApi $openApi) {
    $operation = new Operation(
        operationId: 'uploadTestCoverage',
        tags       : ['TestCoverage'],
        summary    : "Upload test coverage report",
        description: "Upload test coverage report which will be made available in reviews matching the `match`",
        parameters : [
                         new Parameter(
                                     'repositoryId',
                                     'path',
                                     'The id of the repository to which the coverage belongs to.',
                                     true,
                             schema: ['type' => 'integer']
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

                     ]
    );

    $openApi->getPaths()->addPath('/api/test-coverage/{repositoryId}', new PathItem(post: $operation));
};
