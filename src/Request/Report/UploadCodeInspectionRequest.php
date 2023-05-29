<?php
declare(strict_types=1);

namespace DR\Review\Request\Report;

use DigitalRevolution\SymfonyRequestValidation\AbstractValidatedRequest;
use DigitalRevolution\SymfonyRequestValidation\ValidationRules;
use DR\Review\Service\Report\CodeInspection\Parser\CheckStyleIssueParser;
use DR\Review\Service\Report\CodeInspection\Parser\GitlabIssueParser;
use DR\Review\Service\Report\CodeInspection\Parser\JunitIssueParser;

class UploadCodeInspectionRequest extends AbstractValidatedRequest
{
    public function getIdentifier(): string
    {
        return $this->request->query->get('identifier', '');
    }

    public function getBranchId(): string
    {
        return $this->request->query->get('branchId', '');
    }

    public function getBasePath(): string
    {
        return $this->request->query->get('basePath', '');
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
                    'identifier' => 'required|string|min:1|max:50',
                    'branchId'   => 'string|min:1|max:255',
                    'basePath'   => 'string',
                    'format'     => 'string|in:' . CheckStyleIssueParser::FORMAT . ',' . GitlabIssueParser::FORMAT . ',' . JunitIssueParser::FORMAT
                ]
            ]
        );
    }
}
