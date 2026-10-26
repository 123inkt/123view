<?php
declare(strict_types=1);

namespace DR\Review\Request\Report;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Service\Report\CodeInspection\Parser\CheckStyleIssueParser;
use DR\Review\Service\Report\CodeInspection\Parser\GitlabIssueParser;
use DR\Review\Service\Report\CodeInspection\Parser\JunitIssueParser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\Xml;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

class UploadCodeInspectionRequest extends AbstractValidatedRequest
{
    public function getIdentifier(): string
    {
        return $this->request->query->get('identifier', '');
    }

    public function getBranchId(): ?string
    {
        return $this->request->query->get('branchId');
    }

    public function getBasePath(): string
    {
        return $this->request->query->get('basePath', '');
    }

    public function getSubDirectory(): string
    {
        return $this->request->query->get('subDirectory', '');
    }

    public function getFormat(): string
    {
        return $this->request->query->get('format', CheckStyleIssueParser::FORMAT);
    }

    public function getData(): string
    {
        return $this->request->getContent();
    }

    protected function getValidationRules(): ?ValidationRules
    {
        return new ValidationRules(
            [
                'query' => [
                    'identifier'   => 'required|string|min:1|max:50',
                    'branchId'     => 'string|min:1|max:255',
                    'basePath'     => 'string',
                    'subDirectory' => 'string',
                    'format'       => 'string|in:' . CheckStyleIssueParser::FORMAT . ',' . GitlabIssueParser::FORMAT . ',' . JunitIssueParser::FORMAT
                ]
            ]
        );
    }

    protected function validateCustomRules(): ?Response
    {
        $constraint = $this->getFormat() === GitlabIssueParser::FORMAT
            ? new Callback(static function (mixed $data, ExecutionContextInterface $context): void {
                if (is_string($data) === false || json_validate($data) === false) {
                    $context->buildViolation('This value should be a valid GitLab code quality report.')->addViolation();

                    return;
                }

                $issues = json_decode($data, true);
                if (is_array(json_decode($data)) === false || is_array($issues) === false) {
                    $context->buildViolation('This value should be a valid GitLab code quality report.')->addViolation();

                    return;
                }

                foreach ($issues as $issue) {
                    if (is_array($issue) === false || self::isValidGitlabIssue($issue) === false) {
                        $context->buildViolation('This value should be a valid GitLab code quality report.')->addViolation();

                        return;
                    }
                }
            })
            : new Xml(maxSize: PHP_INT_MAX);

        $violations = $this->validator->validate($this->getData(), $constraint);

        return count($violations) > 0 ? $this->handleViolations($violations) : null;
    }

    /**
     * @param array<mixed, mixed> $issue
     */
    private static function isValidGitlabIssue(array $issue): bool
    {
        $location = $issue['location'] ?? [];
        $lines    = is_array($location) ? ($location['lines'] ?? []) : [];

        return (array_key_exists('description', $issue) === false || is_string($issue['description']))
            && (array_key_exists('severity', $issue) === false || is_string($issue['severity']))
            && (array_key_exists('check_name', $issue) === false || $issue['check_name'] === null || is_string($issue['check_name']))
            && (array_key_exists('location', $issue) === false || is_array($location))
            && (is_array($location) === false || array_key_exists('path', $location) === false || is_string($location['path']))
            && (is_array($location) === false || array_key_exists('lines', $location) === false || is_array($lines))
            && (
                is_array($lines) === false
                || array_key_exists('begin', $lines) === false
                || is_int($lines['begin'])
                || (is_string($lines['begin']) && is_numeric($lines['begin']))
            );
    }
}
