<?php

declare(strict_types=1);

namespace DR\Review\Entity\Report\Coverage;

use Doctrine\ORM\Mapping as ORM;
use DR\JBDiff\Util\BitSet;
use DR\Review\Doctrine\Type\BitSetType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Repository\Report\Coverage\FileCoverageRepository;

#[ORM\Entity(repositoryClass: FileCoverageRepository::class)]
class FileCoverage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500)]
    private ?string $filePath = null;

    /**
     * A bitset of all line numbers that are covered
     */
    #[ORM\Column(type: BitSetType::TYPE, length: 500000)]
    private ?BitSet $coverage = null;

    #[ORM\Column]
    private ?int $createTimestamp = null;

    #[ORM\ManyToOne(targetEntity: Repository::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Repository $repository = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): self
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function getCoverage(): BitSet
    {
        return $this->coverage ??= new BitSet();
    }

    public function setCoverage(?BitSet $coverage): self
    {
        $this->coverage = $coverage;

        return $this;
    }

    public function getCreateTimestamp(): ?int
    {
        return $this->createTimestamp;
    }

    public function setCreateTimestamp(?int $createTimestamp): self
    {
        $this->createTimestamp = $createTimestamp;

        return $this;
    }

    public function getRepository(): ?Repository
    {
        return $this->repository;
    }

    public function setRepository(?Repository $repository): FileCoverage
    {
        $this->repository = $repository;

        return $this;
    }
}
