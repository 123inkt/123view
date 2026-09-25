<?php
declare(strict_types=1);

namespace DR\Review\Entity\Git\Diff;

use DR\Review\Utility\MimeTypes;
use SplFileInfo;

class DiffFile
{
    public const FILE_ADDED    = 'file.added';
    public const FILE_DELETED  = 'file.deleted';
    public const FILE_MODIFIED = 'file.modified';

    public ?string $fileModeBefore = null;
    public ?string $fileModeAfter  = null;

    public ?string $filePathBefore = null;
    public ?string $filePathAfter  = null;

    public ?string $hashStart = null;
    public ?string $hashEnd   = null;

    public bool $hasMergeConflict = false;
    public bool $binary           = false;

    public ?string $raw = null;

    /** @var DiffBlock[] */
    private array $blocks = [];

    private ?int $linesAdded   = null;
    private ?int $linesRemoved = null;
    private ?int $linesTotal   = null;

    /**
     * @return DiffBlock[]
     */
    public function getBlocks(): array
    {
        return $this->blocks;
    }

    public function addBlock(DiffBlock $block): void
    {
        $this->blocks[] = $block;
    }

    /**
     * @param DiffBlock[] $blocks
     */
    public function addBlocks(array $blocks): void
    {
        foreach ($blocks as $block) {
            $this->blocks[] = $block;
        }
    }

    public function removeBlocks(): void
    {
        $this->blocks = [];
    }

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

    public function getMimeType(): ?string
    {
        return MimeTypes::getMimeType($this->getPathname());
    }

    public function isImage(): bool
    {
        $mimeType = MimeTypes::getMimeType($this->getPathname());

        return $mimeType !== null && MimeTypes::isImage($mimeType);
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

    public function getFile(): ?SplFileInfo
    {
        if ($this->filePathAfter !== null) {
            return new SplFileInfo($this->filePathAfter);
        }

        if ($this->filePathBefore !== null) {
            return new SplFileInfo($this->filePathBefore);
        }

        return null;
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

    public function getPathname(): string
    {
        return $this->filePathAfter ?? $this->filePathBefore ?? '';
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

    public function getNrOfLinesAdded(): int
    {
        if ($this->linesAdded === null) {
            $this->updateLinesChanged();
        }

        return $this->linesAdded ?? 0;
    }

    public function getNrOfLinesRemoved(): int
    {
        if ($this->linesRemoved === null) {
            $this->updateLinesChanged();
        }

        return $this->linesRemoved ?? 0;
    }

    public function getTotalNrOfLines(): int
    {
        if ($this->linesTotal === null) {
            $this->updateLinesChanged();
        }

        return $this->linesTotal ?? 0;
    }

    /**
     * @return string[]
     */
    public function getLines(): array
    {
        $lines = [];
        foreach ($this->blocks as $block) {
            foreach ($block->lines as $line) {
                if ($line->state === DiffLine::STATE_REMOVED) {
                    continue;
                }
                $lines[] = $line->getLine([DiffChange::REMOVED]);
            }
        }

        return $lines;
    }

    /**
     * For the given block of changes, determine the maximum string length of the line numbers.
     *
     * @param bool $before if true, take the `before` line numbers, `after` otherwise.
     */
    public function getMaxLineNumberLength(bool $before): int
    {
        $length = 0;
        foreach ($this->blocks as $block) {
            foreach ($block->lines as $line) {
                $length = max($length, strlen((string)($before ? $line->lineNumberBefore : $line->lineNumberAfter)));
            }
        }

        return $length;
    }

    public function updateLinesChanged(): void
    {
        $this->linesAdded   = 0;
        $this->linesRemoved = 0;
        $this->linesTotal   = 0;
        foreach ($this->blocks as $block) {
            foreach ($block->lines as $line) {
                if ($line->state === DiffLine::STATE_ADDED || $line->state === DiffLine::STATE_CHANGED) {
                    ++$this->linesAdded;
                }
                if ($line->state === DiffLine::STATE_REMOVED || $line->state === DiffLine::STATE_CHANGED) {
                    ++$this->linesRemoved;
                }
                if ($line->state !== DiffLine::STATE_REMOVED) {
                    ++$this->linesTotal;
                }
            }
        }
    }
}
