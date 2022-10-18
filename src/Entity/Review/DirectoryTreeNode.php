<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Entity\Review;

use LogicException;

/**
 * @template T of object
 */
class DirectoryTreeNode
{
    /**
     * @param DirectoryTreeNode[] $directories
     * @param T[]                 $files
     */
    public function __construct(private string $name, private array $directories = [], private array $files = [])
    {
    }

    public function getName(): string
    {
        return $this->name;
    }

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
     * @return DirectoryTreeNode[]
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * @return T
     */
    public function getFiles(): array
    {
        return $this->files;
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
            $this->directories[] = $subdirectory = new DirectoryTreeNode($path, [], []);
        }

        $subdirectory->addNode($filepath, $item);
    }
}
