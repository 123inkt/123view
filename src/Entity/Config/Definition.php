<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Config;

use Symfony\Component\Serializer\Annotation\SerializedName;

class Definition
{
    /**
     * @SerializedName("subject")
     * @var string[]
     */
    private array $subjects = [];

    /**
     * @SerializedName("file")
     * @var string[]
     */
    private array $files = [];

    /**
     * @SerializedName("author")
     * @var string[]
     */
    private array $authors = [];

    /**
     * @return string[]
     */
    public function getSubjects(): array
    {
        return $this->subjects;
    }

    public function addSubject(string $subject): void
    {
        $this->subjects[] = $subject;
    }

    /**
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function removeSubject(string $subject): void
    {
        // method only required for deserialization
    }

    /**
     * @return string[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    public function addFile(string $file): void
    {
        $this->files[] = $file;
    }

    /**
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function removeFile(string $file): void
    {
        // method only required for deserialization
    }

    /**
     * @return string[]
     */
    public function getAuthors(): array
    {
        return $this->authors;
    }

    public function addAuthor(string $author): void
    {
        $this->authors[] = $author;
    }

    /**
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function removeAuthor(string $author): void
    {
        // method only required for deserialization
    }
}
