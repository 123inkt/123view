<?php

declare(strict_types=1);

namespace DR\Review\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Repository\Report\CodeInspectionIssueRepository;

#[ORM\Entity(repositoryClass: CodeInspectionIssueRepository::class)]
class CodeInspectionIssue
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private ?string $severity = null;

    #[ORM\Column(length: 255)]
    private ?string $file = null;

    #[ORM\Column]
    private ?int $lineNumber = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $rule = null;

    #[ORM\ManyToOne(targetEntity: CodeInspectionReport::class, inversedBy: "issues")]
    #[ORM\JoinColumn(nullable: false)]
    private ?CodeInspectionReport $report = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): CodeInspectionIssue
    {
        $this->id = $id;

        return $this;
    }

    public function getSeverity(): ?string
    {
        return $this->severity;
    }

    public function setSeverity(?string $severity): CodeInspectionIssue
    {
        $this->severity = $severity;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): CodeInspectionIssue
    {
        $this->file = $file;

        return $this;
    }

    public function getLineNumber(): ?int
    {
        return $this->lineNumber;
    }

    public function setLineNumber(?int $lineNumber): CodeInspectionIssue
    {
        $this->lineNumber = $lineNumber;

        return $this;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): CodeInspectionIssue
    {
        $this->message = $message;

        return $this;
    }

    public function getRule(): ?string
    {
        return $this->rule;
    }

    public function setRule(?string $rule): CodeInspectionIssue
    {
        $this->rule = $rule;

        return $this;
    }

    public function getReport(): ?CodeInspectionReport
    {
        return $this->report;
    }

    public function setReport(?CodeInspectionReport $report): CodeInspectionIssue
    {
        $this->report = $report;

        return $this;
    }
}
