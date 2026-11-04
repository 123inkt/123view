<?php
declare(strict_types=1);

namespace DR\Review\Service\Report\CodeInspection\Parser;

use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Service\IO\FilePathNormalizer;
use DR\Utils\Assert;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class GitlabIssueParser implements CodeInspectionIssueParserInterface
{
    public const string FORMAT = 'gitlab';

    public function __construct(private readonly FilePathNormalizer $pathNormalizer)
    {
    }

    public function validate(string $data): bool
    {
        try {
            return is_array(Json::decode($data));
        } catch (JsonException) {
            return false;
        }
    }

    /**
     * @inheritDoc
     * @throws JsonException
     */
    public function parse(string $basePath, string $subDirectory, string $data): array
    {
        $json = Assert::isArray(Json::decode($data, true));

        $issues = [];
        foreach ($json as $error) {
            $description = trim($error['description'] ?? '');
            $severity    = $error['severity'] ?? 'major';
            $filePath    = $this->pathNormalizer->normalize($basePath, $subDirectory, $error['location']['path'] ?? '');
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
