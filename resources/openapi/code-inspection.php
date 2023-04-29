<?php

declare(strict_types=1);

use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\OpenApi;

// phpcs:ignorefile
return static function (OpenApi $openApi) {
    $operation = new Operation(
        operationId: 'uploadCodeInspection',
        tags       : ['Report'],
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

                     ]
    );

    $openApi->getPaths()->addPath('/api/report/code-inspection/{repository}', new PathItem(post: $operation));
};
