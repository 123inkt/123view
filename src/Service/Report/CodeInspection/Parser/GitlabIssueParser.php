<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\CodeInspection\Parser;

use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Service\IO\FilePathNormalizer;
use DR\Review\Utility\Assert;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class GitlabIssueParser implements CodeInspectionIssueParserInterface
{
    public function __construct(private readonly FilePathNormalizer $pathNormalizer)
    {
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function parse(string $basePath, string $data): array
    {
        $json = Assert::isArray(Json::decode($data, true));

        $issues = [];
        foreach ($json as $error) {
            $description = trim($error['description'] ?? '');
            $severity    = $error['severity'] ?? 'major';
            $filePath    = $this->pathNormalizer->normalize($basePath, $error['location']['path'] ?? '');
            $lineNumber  = (int)($error['location']['lines']['begin'] ?? 0);
            $rule        = $error['check_name'] ?? null;

            if ($description === '' || $filePath === '') {
                continue;
            }

            $issues[] = $issue = new CodeInspectionIssue();
            $issue->setFile($filePath);
            $issue->setLineNumber($lineNumber);
            $issue->setMessage($description);
            $issue->setSeverity($severity);
            $issue->setRule($rule);
        }

        return $issues;
    }
}
