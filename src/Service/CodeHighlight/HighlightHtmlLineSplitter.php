<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\CodeHighlight;

use DR\GitCommitNotification\Utility\Assert;

class HighlightHtmlLineSplitter
{
    /**
     * @return string[]
     */
    public function split(string $data): array
    {
        preg_match_all('/<\/?span.*?>/', $data, $tags);
        $texts = Assert::isArray(preg_split('/<\/?span.*?>/', $data));

        $length = count($texts);
        $stack  = [];
        $lines  = [];
        $line   = '';

        for ($i = 0; $i < $length; $i++) {
            $tag  = $tags[0][$i] ?? '';
            $text = $texts[$i];

            if (str_contains($text, "\n") === false) {
                $line .= $text;
            } else {
                $splitLines = $this->splitLines($stack, $text);
                $nrOfLines  = count($splitLines);
                foreach ($splitLines as $index => $splitLine) {
                    if ($index === 0) {
                        $lines[] = $line . $splitLine;
                        $line    = '';
                    } elseif ($index === $nrOfLines - 1) {
                        $line = $splitLine;
                    } else {
                        $lines[] = $splitLine;
                    }
                }
            }

            $line .= $tag;

            // increment/decrement tag stack
            if (str_starts_with($tag, '<span')) {
                $stack[] = $tag;
            } elseif (str_starts_with($tag, '</span')) {
                array_pop($stack);
            }
        }

        if ($line !== '') {
            $lines[] = $line;
        }

        return $lines;
    }

    /**
     * @param string[] $stack
     *
     * @return string[]
     */
    private function splitLines(array $stack, string $string): array
    {
        $lines = explode("\n", $string);

        $length = count($lines);
        $result = [];
        foreach ($lines as $index => $line) {
            // for the first line we should close all open tags
            if ($index === 0) {
                $result[] = $line . str_repeat('</span>', count($stack));
                continue;
            }

            // if string is only whitespace no need to add tags
            if ($index < $length - 1 && trim($line) === '') {
                $result[] = $line;
                continue;
            }

            // add all opening tags at the start
            $line = implode('', $stack) . $line;

            // add all closing tags when this is not the last line
            if ($index < $length - 1) {
                $line .= str_repeat('</span>', count($stack));
            }
            $result[] = $line;
        }

        return $result;
    }
}
