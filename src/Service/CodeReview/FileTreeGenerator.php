<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeReview;

use DR\GitCommitNotification\Entity\Git\Diff\DiffFile;
use DR\GitCommitNotification\Entity\Review\DirectoryTreeNode;

class FileTreeGenerator
{
    /**
     * @param DiffFile[] $diffFiles
     */
    public function generate(array $diffFiles): DirectoryTreeNode
    {
        /** @var DirectoryTreeNode<DiffFile> $node */
        $node = new DirectoryTreeNode('root');
        foreach ($diffFiles as $file) {
            $filepath = explode('/', trim($file->getFile()?->getPathname(), '/'));

            $node->addNode($filepath, $file);
        }

        return $node;
    }
}
