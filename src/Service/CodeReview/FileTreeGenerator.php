<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Model\Review\DirectoryTreeNode;

class FileTreeGenerator
{
    /**
     * @param DiffFile[] $diffFiles
     * @return DirectoryTreeNode<DiffFile>
     */
    public function generate(array $diffFiles): DirectoryTreeNode
    {
        /** @var DirectoryTreeNode<DiffFile> $node */
        $node = new DirectoryTreeNode('root');
        foreach ($diffFiles as $file) {
            $filepath = explode('/', trim((string)$file->getFile()?->getPathname(), '/'));

            $node->addNode($filepath, $file);
        }

        return $node;
    }
}
