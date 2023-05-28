<?php

declare(strict_types=1);

namespace DR\Review\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Doctrine\Type\LineCoverageType;
use DR\Review\Repository\Report\CodeCoverageFileRepository;

#[ORM\Entity(repositoryClass: CodeCoverageFileRepository::class)]
#[ORM\Index(columns: ['report_id', 'file'], name: 'report_filepath')]
class CodeCoverageFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private string $file;

    /**
     * Binary data: will be stored as json encoded + gzcompress
     */
    #[ORM\Column(type: LineCoverageType::TYPE, length: 60000)]
    private LineCoverage $coverage;

    #[ORM\ManyToOne(targetEntity: CodeCoverageReport::class)]
    #[ORM\JoinColumn(nullable: false)]
    private CodeCoverageReport $report;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function setFile(string $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getCoverage(): LineCoverage
    {
        return $this->coverage;
    }

    public function setCoverage(LineCoverage $coverage): self
    {
        $this->coverage = $coverage;

        return $this;
    }

    public function getReport(): CodeCoverageReport
    {
        return $this->report;
    }

    public function setReport(CodeCoverageReport $report): self
    {
        $this->report = $report;

        return $this;
    }
}
