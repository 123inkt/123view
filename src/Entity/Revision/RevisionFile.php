<?php

declare(strict_types=1);

namespace DR\Review\Entity\Revision;

use Doctrine\ORM\Mapping as ORM;
use DR\Review\Repository\Revision\RevisionFileRepository;

#[ORM\Entity(repositoryClass: RevisionFileRepository::class)]
class RevisionFile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    #[ORM\ManyToOne(targetEntity: Revision::class, cascade: ['persist'], inversedBy: 'files')]
    #[ORM\JoinColumn(nullable: false)]
    private Revision $revision;

    #[ORM\Column]
    private int $linesAdded;

    #[ORM\Column]
    private int $linesRemoved;

    #[ORM\Column(length: 500)]
    private string $filepath;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): RevisionFile
    {
        $this->id = $id;

        return $this;
    }

    public function getRevision(): Revision
    {
        return $this->revision;
    }

    public function setRevision(Revision $revision): RevisionFile
    {
        $this->revision = $revision;

        return $this;
    }

    public function getLinesAdded(): int
    {
        return $this->linesAdded;
    }

    public function setLinesAdded(int $linesAdded): RevisionFile
    {
        $this->linesAdded = $linesAdded;

        return $this;
    }

    public function getLinesRemoved(): int
    {
        return $this->linesRemoved;
    }

    public function setLinesRemoved(int $linesRemoved): RevisionFile
    {
        $this->linesRemoved = $linesRemoved;

        return $this;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
    }

    public function setFilepath(string $filepath): RevisionFile
    {
        $this->filepath = $filepath;

        return $this;
    }
}
