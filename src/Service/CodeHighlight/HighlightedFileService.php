<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeHighlight;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Model\Review\Highlight\HighlightedFile;
use Exception;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class HighlightedFileService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** To ensure performance, skip highlighting for files larger than */
    public const MAX_LINE_COUNT = 3000;

    public function __construct(
        private readonly FilenameToLanguageTranslator $translator,
        private readonly HttpClientInterface $highlightjsClient,
        private readonly HighlightHtmlLineSplitter $splitter,
        private readonly HighlightedFilePreprocessor $preprocessor
    ) {
    }

    /**
     * @throws Exception|TransportExceptionInterface
     */
    public function fromDiffFile(DiffFile $diffFile): ?HighlightedFile
    {
        $languageName = $this->translator->translate($diffFile->getPathname());
        if ($languageName === null || $diffFile->getTotalNrOfLines() >= self::MAX_LINE_COUNT || count($diffFile->getBlocks()) > 1) {
            return null;
        }

        $content = implode("\n", $diffFile->getLines());

        // preprocess certain contents that breaks the highlightjs formatter
        $content = $this->preprocessor->process($languageName, $content);

        try {
            $response = $this->highlightjsClient->request('POST', '', ['query' => ['language' => $languageName], 'body' => $content]);
        } catch (Throwable $exception) {
            $this->logger?->info('Failed to get code highlighting: ' . $exception->getMessage());

            return null;
        }

        if ($response->getStatusCode() !== Response::HTTP_OK) {
            $this->logger?->info('Failed to get code highlighting: ' . $response->getContent(false));

            return null;
        }

        return new HighlightedFile(
            $diffFile->getPathname(),
            fn() => $this->splitter->split($response->getContent(false))
        );
    }
}
