<?php
declare(strict_types=1);

namespace DR\Review\Model\Review;

use DR\Utils\Arrays;
use Iterator;
use LogicException;

/**
 * @template T of object
 */
class DirectoryTreeNode
{
    /**
     * @param DirectoryTreeNode<T>|null $parentNode
     * @param DirectoryTreeNode<T>[]    $directories
     * @param T[]                       $files
     */
    public function __construct(
        private string $name,
        private ?DirectoryTreeNode $parentNode = null,
        private array $directories = [],
        private array $files = []
    ) {
        foreach ($this->directories as $directory) {
            $directory->parentNode = $this;
        }
    }

    public function isEmpty(): bool
    {
        return count($this->directories) === 0 && count($this->files) === 0;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPathname(): string
    {
        $path = [];
        for ($node = $this; $node->parentNode !== null; $node = $node->parentNode) {
            array_unshift($path, $node->name);
        }

        return implode('/', $path);
    }

    /**
     * @return DirectoryTreeNode<T>|null
     */
    public function getDirectory(string $path): ?self
    {
        foreach ($this->directories as $directory) {
            if ($directory->getName() === $path) {
                return $directory;
            }
        }

        return null;
    }

    /**
     * @return DirectoryTreeNode<T>[]
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * @return T|null
     */
    public function getFirstFileInTree(): ?object
    {
        if (count($this->directories) === 0) {
            return Arrays::firstOrNull($this->files);
        }

        foreach ($this->directories as $directory) {
            $result = $directory->getFirstFileInTree();
            if ($result !== null) {
                return $result;
            }
        }

        return null;
    }

    /**
     * @return T[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return T[]
     */
    public function getFilesRecursive(): array
    {
        $files = [$this->files];

        foreach ($this->directories as $directory) {
            $files[] = $directory->getFilesRecursive();
        }

        return array_merge(...$files);
    }

    /**
     * @return DirectoryTreeNode<T>
     */
    public function flatten(): self
    {
        // keep flattening till we have multiple children
        while (true) {
            if ($this->name === 'root' || count($this->directories) !== 1 || count($this->files) !== 0) {
                break;
            }
            $subdirectory      = $this->directories[0];
            $this->directories = $subdirectory->getDirectories();
            foreach ($this->directories as $directory) {
                $directory->parentNode = $this;
            }
            $this->files = $subdirectory->getFiles();
            $this->name  .= '/' . $subdirectory->getName();
        }

        // flatten subdirectories
        foreach ($this->directories as $directory) {
            $directory->flatten();
        }

        return $this;
    }

    /**
     * @return DirectoryTreeNode<T>
     */
    public function sort(callable $sorter): self
    {
        foreach ($this->directories as $directory) {
            $directory->sort($sorter);
        }

        if (count($this->files) > 0) {
            usort($this->files, $sorter);
        }

        return $this;
    }

    /**
     * @param string[] $filepath
     * @param T        $item
     */
    public function addNode(array $filepath, object $item): void
    {
        if (count($filepath) === 0) {
            throw new LogicException('AddNode cant receive an empty filepath');
        }

        if (count($filepath) === 1) {
            $this->files[] = $item;

            return;
        }

        /** @var string $path */
        $path         = array_shift($filepath);
        $subdirectory = $this->getDirectory($path);
        if ($subdirectory === null) {
            $this->directories[] = $subdirectory = new DirectoryTreeNode($path, $this);
        }

        $subdirectory->addNode($filepath, $item);
    }

    /**
     * @return Iterator<T>
     */
    public function getFileIterator(): Iterator
    {
        foreach ($this->directories as $directory) {
            foreach ($directory->getFileIterator() as $file) {
                yield $file;
            }
        }

        foreach ($this->files as $file) {
            yield $file;
        }
    }
}
