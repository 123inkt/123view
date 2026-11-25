<?php
declare(strict_types=1);

namespace DR\Review\Service\SummaryNote;

use DR\Review\Model\Api\Gitlab\Note;
use DR\Review\Model\SummaryNote\MergeRequestSummaryNote;
use DR\Review\Model\SummaryNote\SummaryNoteStatus;
use DR\Review\Service\Markdown\MarkdownConverterService;
use League\CommonMark\Exception\CommonMarkException;
use Psr\Log\LoggerInterface;

readonly class MergeRequestSummaryNoteFactory
{
    public function __construct(
        private string $summaryNoteSuccessPattern,
        private string $summaryNoteWarningPattern,
        private string $summaryNoteProgressPattern,
        private MarkdownConverterService $markdownConverter,
        private LoggerInterface $logger
    ) {
    }

    /**
     * @throws CommonMarkException
     */
    public function create(Note $note, string $mergeRequestUrl): MergeRequestSummaryNote
    {
        $status = SummaryNoteStatus::Unknown;
        $title  = $note->author->name ?? 'Summary';

        $patterns = [
            [SummaryNoteStatus::Success, $this->summaryNoteSuccessPattern],
            [SummaryNoteStatus::Warning, $this->summaryNoteWarningPattern],
            [SummaryNoteStatus::Progress, $this->summaryNoteProgressPattern],
        ];
        foreach ($patterns as [$patternStatus, $pattern]) {
            $matches = $this->match($pattern, $note->body);
            if ($matches === null) {
                continue;
            }

            $status = $patternStatus;
            $title = ($matches[1] ?? '') === '' ? $title : $matches[1];
            break;
        }

        $body = str_replace('](#note_', '](' . $mergeRequestUrl . '#note_', $note->body);

        return new MergeRequestSummaryNote($status, $title, $this->markdownConverter->convert($body), $mergeRequestUrl . '#note_' . $note->id);
    }

    /**
     * @return string[]|null
     */
    private function match(string $pattern, string $body): ?array
    {
        if ($pattern === '') {
            return null;
        }

        $result = @preg_match($pattern, $body, $matches);
        if ($result === false) {
            $this->logger->warning('Invalid summary note pattern: {pattern}', ['pattern' => $pattern, 'error' => preg_last_error_msg()]);
        }

        return $result === 1 ? $matches : null;
    }
}
