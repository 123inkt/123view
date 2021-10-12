<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Git\Diff;

class DiffFile
{
    public const FILE_ADDED    = 'file.added';
    public const FILE_DELETED  = 'file.deleted';
    public const FILE_MODIFIED = 'file.modified';

    public ?string $filePathBefore = null;
    public ?string $filePathAfter  = null;

    /** @var DiffBlock[] */
    public array $blocks = [];

    public function isAdded(): bool
    {
        return $this->filePathBefore === null;
    }

    public function isDeleted(): bool
    {
        return $this->filePathAfter === null;
    }

    public function isModified(): bool
    {
        return $this->filePathBefore !== null && $this->filePathAfter !== null;
    }

    public function isRename(): bool
    {
        if ($this->filePathBefore === null || $this->filePathAfter === null) {
            return false;
        }

        return $this->filePathBefore !== $this->filePathAfter;
    }

    public function getFileMode(): string
    {
        if ($this->isModified()) {
            return self::FILE_MODIFIED;
        }

        if ($this->isAdded()) {
            return self::FILE_ADDED;
        }

        return self::FILE_DELETED;
    }

    public function getExtension(): string
    {
        $filepath = $this->filePathAfter ?? $this->filePathBefore ?? '';

        return pathinfo($filepath, PATHINFO_EXTENSION);
    }

    public function getFilename(): string
    {
        if ($this->filePathAfter !== null) {
            return basename($this->filePathAfter);
        }

        if ($this->filePathBefore !== null) {
            return basename($this->filePathBefore);
        }

        return '';
    }

    public function getDirname(): string
    {
        if ($this->filePathAfter !== null) {
            return dirname($this->filePathAfter);
        }

        if ($this->filePathBefore !== null) {
            return dirname($this->filePathBefore);
        }

        return '';
    }
}
