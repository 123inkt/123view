<?php

declare(strict_types=1);

namespace DR\Review\Entity\Report;

use Doctrine\ORM\Mapping as ORM;
use DR\JBDiff\Util\BitSet;
use DR\Review\Doctrine\Type\BitSetType;
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
    private ?string $file = null;

    /**
     * BitSet size calculation:
     * => a file of max 64000 lines.
     * => store each line number inside the BitSet
     * => requires 64000 / 64 = 10000 words inside the BitSet
     * => one 64-bit word can be stored into 4 bytes.
     * => storage: 10000 * 4 = 40000 bytes.
     */
    #[ORM\Column(type: BitSetType::TYPE, length: 40000)]
    private ?BitSet $coverage = null;

    #[ORM\ManyToOne(targetEntity: CodeCoverageReport::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?CodeCoverageReport $report = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getFile(): ?string
    {
        return $this->file;
    }

    public function setFile(?string $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function getCoverage(): ?BitSet
    {
        return $this->coverage;
    }

    public function setCoverage(?BitSet $coverage): self
    {
        $this->coverage = $coverage;

        return $this;
    }

    public function getReport(): ?CodeCoverageReport
    {
        return $this->report;
    }

    public function setReport(?CodeCoverageReport $report): self
    {
        $this->report = $report;

        return $this;
    }
}
