<?php

declare(strict_types=1);

use ApiPlatform\OpenApi\Model\MediaType;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\RequestBody;
use ApiPlatform\OpenApi\Model\Response;
use ApiPlatform\OpenApi\OpenApi;
use DR\Review\Service\Report\CodeInspection\Parser\CheckStyleIssueParser;
use DR\Review\Service\Report\CodeInspection\Parser\GitlabIssueParser;
use DR\Review\Service\Report\CodeInspection\Parser\JunitIssueParser;

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
            200 => new Response('When the report was successfully created. Responds with count of created issues.'),
            204 => new Response('When the report has no issues.'),
            400 => new Response('On any invalid arguments.'),
            404 => new Response('When the repository with the given `name` cant be found.'),
        ],
        summary    : "Upload code inspection report",
        description: "Upload code inspection report in varies formats. **Requires ADMIN privileges.**",
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
                'identifier',
                'query',
                'The identifier of the type of the report. Only one type per commit hash is possible. Ex: `phpstan`, `phpcs`, ..',
                true,
                schema: ['type' => 'string', 'minLength' => 1, 'maxLength' => 50]
            ),
            new Parameter(
                'basePath',
                'query',
                'The `basePath` to subtract from the filenames in the report.',
                schema: ['type' => 'string', 'maxLength' => 500]
            ),
            new Parameter(
                'format',
                'query',
                sprintf('The format of the input. Defaults to `%s`.', CheckStyleIssueParser::FORMAT),
                schema: [
                    'type' => 'string',
                    'enum' => [
                        CheckStyleIssueParser::FORMAT,
                        GitlabIssueParser::FORMAT,
                        JunitIssueParser::FORMAT
                    ]
                ]
            )

        ],
        requestBody: new RequestBody(
            'The `xml` or `json` of the code inspection specified by the `format`.',
            $content,
            true
        )
    );

    $openApi->getPaths()->addPath('/api/report/code-inspection/{repository}/{commitHash}', new PathItem(post: $operation));
};
