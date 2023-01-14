<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Model\Review\DirectoryTreeNode;

class FileTreeGenerator
{
    /**
     * @param DiffFile[] $diffFiles
     *
     * @return DirectoryTreeNode<DiffFile>
     */
    public function generate(array $diffFiles): DirectoryTreeNode
    {
        /** @var DirectoryTreeNode<DiffFile> $node */
        $node = new DirectoryTreeNode('root');
        foreach ($diffFiles as $file) {
            // create shallow copy of the file diff
            $fileClone = clone $file;
            $fileClone->removeBlocks();

            $filepath = explode('/', trim((string)$fileClone->getFile()?->getPathname(), '/'));

            $node->addNode($filepath, $file);
        }

        return $node;
    }
}
