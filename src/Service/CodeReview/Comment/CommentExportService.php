<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Comment;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\Show\GitShowService;
use Throwable;

class CommentExportService
{
    /** @var array<string, string[]> */
    private array $fileCache = [];

    /** @var array<string, true> */
    private array $failedKeys = [];

    public function __construct(private readonly GitShowService $gitShowService)
    {
    }

    public function generateMarkdown(Comment $comment): string
    {
        $lineRef    = $comment->getLineReference();
        $review     = $comment->getReview();
        $repository = $review->getRepository();

        $hash = $lineRef->headSha ?? $this->getLastRevisionHash($review->getRevisions()->toArray());

        $contextBlock = $this->buildContextBlock($repository, $hash, $comment->getFilePath(), $lineRef->line);

        $md  = "# Comment #{$comment->getId()}\n\n";
        $md .= "**File:** `{$comment->getFilePath()}`  \n";
        $md .= "**Line:** {$lineRef->line}\n\n";
        $md .= "---\n\n";

        $user = $comment->getUser();
        $date = date('Y-m-d H:i:s', $comment->getCreateTimestamp());
        $md  .= "## {$user->getName()} — {$date}\n\n";
        $md  .= $comment->getMessage() . "\n\n";

        $replies = $comment->getReplies();
        if ($replies->count() > 0) {
            $md .= "---\n\n### Replies\n\n";
            foreach ($replies as $reply) {
                $replyUser = $reply->getUser();
                $replyDate = date('Y-m-d H:i:s', $reply->getCreateTimestamp());
                $md       .= "#### {$replyUser->getName()} — {$replyDate}\n\n";
                $md       .= $reply->getMessage() . "\n\n";
            }
        }

        if ($contextBlock !== null) {
            $md .= $contextBlock;
        }

        return $md;
    }

    public function clearCache(): void
    {
        $this->fileCache  = [];
        $this->failedKeys = [];
    }

    private function buildContextBlock(Repository $repository, ?string $hash, string $filePath, int $commentLine): ?string
    {
        if ($hash === null) {
            return null;
        }

        $cacheKey = $repository->getId() . ':' . $hash . ':' . $filePath;

        if (isset($this->failedKeys[$cacheKey])) {
            return null;
        }

        if (!array_key_exists($cacheKey, $this->fileCache)) {
            try {
                $content                    = $this->gitShowService->getFileContentsByHash($repository, $hash, $filePath);
                $this->fileCache[$cacheKey] = explode("\n", $content);
            } catch (Throwable) {
                $this->failedKeys[$cacheKey] = true;

                return null;
            }
        }

        $lines      = $this->fileCache[$cacheKey];
        $lineCount  = count($lines);
        $startIndex = max(0, $commentLine - 51);       // 50 lines before (0-based)
        $endIndex   = min($lineCount - 1, $commentLine + 49); // 50 lines after

        $contextLines = array_slice($lines, $startIndex, $endIndex - $startIndex + 1);
        $language     = $this->detectLanguage($filePath);

        $md  = "---\n\n## File Context\n\n";
        $md .= sprintf("`%s` (lines %d–%d)\n\n", $filePath, $startIndex + 1, $endIndex + 1);
        $md .= "```{$language}\n";
        $md .= implode("\n", $contextLines);
        $md .= "\n```\n";

        return $md;
    }

    /**
     * @param Revision[] $revisions
     */
    private function getLastRevisionHash(array $revisions): ?string
    {
        if ($revisions === []) {
            return null;
        }

        usort($revisions, static fn(Revision $a, Revision $b) => $b->getCreateTimestamp() <=> $a->getCreateTimestamp());

        return $revisions[0]->getCommitHash();
    }

    private function detectLanguage(string $filePath): string
    {
        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        return match ($ext) {
            'php'        => 'php',
            'ts'         => 'typescript',
            'tsx'        => 'tsx',
            'js'         => 'javascript',
            'jsx'        => 'jsx',
            'twig', 'html', 'htm' => 'html',
            'css'        => 'css',
            'scss', 'sass' => 'scss',
            'json'       => 'json',
            'yaml', 'yml' => 'yaml',
            'xml'        => 'xml',
            'sh', 'bash' => 'bash',
            'sql'        => 'sql',
            'md'         => 'markdown',
            'go'         => 'go',
            'py'         => 'python',
            'java'       => 'java',
            'rb'         => 'ruby',
            'rs'         => 'rust',
            default      => ''
        };
    }
}
