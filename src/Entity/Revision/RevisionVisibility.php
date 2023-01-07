<?php

namespace DR\Review\Entity\Revision;

use DR\Review\Repository\Revision\RevisionVisibilityRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RevisionVisibilityRepository::class)]
class RevisionVisibility
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }
}
